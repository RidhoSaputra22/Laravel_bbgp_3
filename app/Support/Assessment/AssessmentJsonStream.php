<?php

namespace App\Support\Assessment;

use RuntimeException;

/**
 * Reads the assessment package without loading the complete JSON document.
 * The package format deliberately keeps `data.forms` and `forms.*.fields`
 * as the last member of their object so each item can be handed to a caller
 * as soon as its metadata is available.
 */
class AssessmentJsonStream
{
    private const BUFFER_SIZE = 8192;

    private string $buffer = '';

    private int $position = 0;

    private bool $eof = false;

    private function __construct(private $handle) {}

    public static function open(string $path): self
    {
        $handle = @fopen($path, 'rb');

        if (! is_resource($handle)) {
            throw new RuntimeException('File JSON assessment tidak dapat dibuka.');
        }

        return new self($handle);
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * @param  callable(array<string,mixed>):void|null  $onAssessment
     * @param  callable(array<string,mixed>,int):void|null  $onForm
     * @param  callable(array<string,mixed>,int,int):void|null  $onField
     * @param  callable(array<string,mixed>,int):void|null  $onAssignment
     * @return array{meta:array<string,mixed>,assessment:array<string,mixed>,counts:array{forms:int,fields:int,assignments:int}}
     */
    public function scan(
        ?callable $onAssessment = null,
        ?callable $onForm = null,
        ?callable $onField = null,
        ?callable $onAssignment = null
    ): array {
        $meta = [];
        $assessment = [];
        $counts = ['forms' => 0, 'fields' => 0, 'assignments' => 0];
        $seenRootKeys = [];
        $dataSeen = false;
        $includedSeen = false;

        try {
            $this->expect('{');

            if ($this->peekNonWhitespace() === '}') {
                $this->readChar();
                throw new RuntimeException('JSON assessment tidak boleh kosong.');
            }

            while (true) {
                $key = $this->readString();

                if (isset($seenRootKeys[$key])) {
                    throw $this->error("Properti root '$key' tidak boleh berulang.");
                }

                $seenRootKeys[$key] = true;
                $this->expect(':');

                if ($key === 'meta') {
                    $meta = $this->readObject();
                } elseif ($key === 'data') {
                    if ($dataSeen) {
                        throw $this->error('Properti data tidak boleh berulang.');
                    }

                    $dataSeen = true;
                    $assessment = $this->readAssessment(
                        $onAssessment,
                        $onForm,
                        $onField,
                        $counts
                    );
                } elseif ($key === 'included') {
                    if (! $dataSeen) {
                        throw $this->error('Properti data harus dibaca sebelum included.');
                    }

                    $includedSeen = true;
                    $this->readIncluded($onAssignment, $counts);
                } else {
                    throw $this->error("Properti root '$key' tidak dikenali.");
                }

                $delimiter = $this->readDelimiter('}');

                if ($delimiter === '}') {
                    break;
                }
            }

            if (! $dataSeen) {
                throw $this->error('Properti data wajib tersedia.');
            }

            $this->ensureEndOfDocument();
        } finally {
            $this->close();
        }

        return compact('meta', 'assessment', 'counts');
    }

    /**
     * @param  callable(array<string,mixed>):void|null  $onAssessment
     * @param  callable(array<string,mixed>,int):void|null  $onForm
     * @param  callable(array<string,mixed>,int,int):void|null  $onField
     * @param  array{forms:int,fields:int,assignments:int}  $counts
     * @return array<string,mixed>
     */
    private function readAssessment(
        ?callable $onAssessment,
        ?callable $onForm,
        ?callable $onField,
        array &$counts
    ): array {
        $this->expect('{');
        $assessment = [];
        $seenKeys = [];
        $formsSeen = false;

        while (true) {
            $key = $this->readString();

            if (isset($seenKeys[$key])) {
                throw $this->error("Properti data.$key tidak boleh berulang.");
            }

            $seenKeys[$key] = true;
            $this->expect(':');

            if ($key === 'forms') {
                if ($formsSeen) {
                    throw $this->error('Properti data.forms tidak boleh berulang.');
                }

                $formsSeen = true;
                if ($onAssessment) {
                    $onAssessment($assessment);
                }
                $this->readForms($onForm, $onField, $counts);
            } else {
                if ($formsSeen) {
                    throw $this->error('Properti data.forms harus menjadi properti terakhir agar dapat diproses streaming.');
                }

                $assessment[$key] = $this->readValue();
            }

            $delimiter = $this->readDelimiter('}');

            if ($delimiter === '}') {
                break;
            }
        }

        if (! $formsSeen) {
            throw $this->error('Properti data.forms wajib tersedia.');
        }

        return $assessment;
    }

    /**
     * @param  callable(array<string,mixed>,int):void|null  $onForm
     * @param  callable(array<string,mixed>,int,int):void|null  $onField
     * @param  array{forms:int,fields:int,assignments:int}  $counts
     */
    private function readForms(?callable $onForm, ?callable $onField, array &$counts): void
    {
        $this->expect('[');

        if ($this->peekNonWhitespace() === ']') {
            $this->readChar();

            return;
        }

        $formIndex = 0;

        while (true) {
            $this->expect('{');
            $form = [];
            $seenKeys = [];
            $fieldsSeen = false;

            while (true) {
                $key = $this->readString();

                if (isset($seenKeys[$key])) {
                    throw $this->error("Properti data.forms.$formIndex.$key tidak boleh berulang.");
                }

                $seenKeys[$key] = true;
                $this->expect(':');

                if ($key === 'fields') {
                    $fieldsSeen = true;
                    if ($onForm) {
                        $onForm($form, $formIndex);
                    }
                    $this->readFields($onField, $formIndex, $counts);
                } else {
                    if ($fieldsSeen) {
                        throw $this->error("Properti data.forms.$formIndex.fields harus menjadi properti terakhir agar dapat diproses streaming.");
                    }

                    $form[$key] = $this->readValue();
                }

                $delimiter = $this->readDelimiter('}');

                if ($delimiter === '}') {
                    break;
                }
            }

            if (! $fieldsSeen) {
                throw $this->error("Properti data.forms.$formIndex.fields wajib tersedia.");
            }

            $counts['forms']++;
            $formIndex++;
            $delimiter = $this->readDelimiter(']');

            if ($delimiter === ']') {
                break;
            }
        }
    }

    /**
     * @param  callable(array<string,mixed>,int,int):void|null  $onField
     * @param  array{forms:int,fields:int,assignments:int}  $counts
     */
    private function readFields(?callable $onField, int $formIndex, array &$counts): void
    {
        $this->expect('[');

        if ($this->peekNonWhitespace() === ']') {
            $this->readChar();

            return;
        }

        $fieldIndex = 0;

        while (true) {
            $field = $this->readObject();
            if ($onField) {
                $onField($field, $formIndex, $fieldIndex);
            }
            $counts['fields']++;
            $fieldIndex++;
            $delimiter = $this->readDelimiter(']');

            if ($delimiter === ']') {
                break;
            }
        }
    }

    /**
     * @param  callable(array<string,mixed>,int):void|null  $onAssignment
     * @param  array{forms:int,fields:int,assignments:int}  $counts
     */
    private function readIncluded(?callable $onAssignment, array &$counts): void
    {
        $this->expect('{');
        $seenKeys = [];

        if ($this->peekNonWhitespace() === '}') {
            $this->readChar();

            return;
        }

        while (true) {
            $key = $this->readString();

            if (isset($seenKeys[$key])) {
                throw $this->error("Properti included.$key tidak boleh berulang.");
            }

            $seenKeys[$key] = true;
            $this->expect(':');

            if ($key !== 'assignment_configs') {
                throw $this->error("Properti included.$key tidak dikenali.");
            }

            $this->expect('[');

            if ($this->peekNonWhitespace() !== ']') {
                $assignmentIndex = 0;

                while (true) {
                    $assignment = $this->readObject();
                    if ($onAssignment) {
                        $onAssignment($assignment, $assignmentIndex);
                    }
                    $counts['assignments']++;
                    $assignmentIndex++;
                    $delimiter = $this->readDelimiter(']');

                    if ($delimiter === ']') {
                        break;
                    }
                }
            } else {
                $this->readChar();
            }

            $delimiter = $this->readDelimiter('}');

            if ($delimiter === '}') {
                break;
            }
        }
    }

    /** @return array<string,mixed> */
    private function readObject(): array
    {
        $this->expect('{');
        $result = [];
        $seenKeys = [];

        if ($this->peekNonWhitespace() === '}') {
            $this->readChar();

            return $result;
        }

        while (true) {
            $key = $this->readString();

            if (isset($seenKeys[$key])) {
                throw $this->error("Properti '$key' tidak boleh berulang.");
            }

            $seenKeys[$key] = true;
            $this->expect(':');
            $result[$key] = $this->readValue();
            $delimiter = $this->readDelimiter('}');

            if ($delimiter === '}') {
                break;
            }
        }

        return $result;
    }

    private function readValue(): mixed
    {
        $character = $this->peekNonWhitespace();

        return match ($character) {
            '{' => $this->readObject(),
            '[' => $this->readArray(),
            '"' => $this->readString(),
            default => $this->readLiteral(),
        };
    }

    /** @return array<int,mixed> */
    private function readArray(): array
    {
        $this->expect('[');
        $result = [];

        if ($this->peekNonWhitespace() === ']') {
            $this->readChar();

            return $result;
        }

        while (true) {
            $result[] = $this->readValue();
            $delimiter = $this->readDelimiter(']');

            if ($delimiter === ']') {
                break;
            }
        }

        return $result;
    }

    private function readString(): string
    {
        $this->expect('"');
        $raw = '"';

        while (true) {
            $character = $this->readChar();

            if ($character === null) {
                throw $this->error('String JSON tidak selesai.');
            }

            $raw .= $character;

            if ($character === '\\') {
                $escaped = $this->readChar();

                if ($escaped === null) {
                    throw $this->error('Escape string JSON tidak selesai.');
                }

                $raw .= $escaped;

                if ($escaped === 'u') {
                    for ($index = 0; $index < 4; $index++) {
                        $hex = $this->readChar();

                        if ($hex === null || ! preg_match('/^[0-9a-fA-F]$/', $hex)) {
                            throw $this->error('Unicode escape string JSON tidak valid.');
                        }

                        $raw .= $hex;
                    }
                }

                continue;
            }

            if ($character === '"') {
                $value = json_decode($raw, true);

                if (! is_string($value) && $value !== '') {
                    throw $this->error('String JSON tidak valid.');
                }

                return (string) $value;
            }

            if (ord($character) < 0x20) {
                throw $this->error('String JSON berisi karakter kontrol.');
            }
        }
    }

    private function readLiteral(): mixed
    {
        $literal = '';

        while (($character = $this->peekChar()) !== null) {
            if (ctype_space($character) || in_array($character, [',', ']', '}'], true)) {
                break;
            }

            $literal .= $this->readChar();
        }

        if ($literal === '') {
            throw $this->error('Nilai JSON tidak ditemukan.');
        }

        $value = json_decode($literal, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw $this->error('Nilai JSON tidak valid: '.json_last_error_msg());
        }

        return $value;
    }

    private function readDelimiter(string $end): string
    {
        $character = $this->peekNonWhitespace();

        if ($character === ',') {
            $this->readChar();

            return ',';
        }

        if ($character === $end) {
            $this->readChar();

            return $end;
        }

        throw $this->error("Pemisah JSON tidak valid, mengharapkan ',' atau '$end'.");
    }

    private function expect(string $expected): void
    {
        $character = $this->peekNonWhitespace();

        if ($character !== $expected) {
            throw $this->error("JSON tidak valid, mengharapkan '$expected'.");
        }

        $this->readChar();
    }

    private function ensureEndOfDocument(): void
    {
        if ($this->peekNonWhitespace() !== null) {
            throw $this->error('Terdapat data setelah akhir JSON.');
        }
    }

    private function peekNonWhitespace(): ?string
    {
        do {
            $character = $this->peekChar();

            if ($character === null) {
                return null;
            }

            if (! ctype_space($character)) {
                return $character;
            }

            $this->readChar();
        } while (true);
    }

    private function peekChar(): ?string
    {
        $this->fillBuffer();

        return $this->buffer === '' ? null : $this->buffer[0];
    }

    private function readChar(): ?string
    {
        $this->fillBuffer();

        if ($this->buffer === '') {
            return null;
        }

        $character = $this->buffer[0];
        $this->buffer = substr($this->buffer, 1);
        $this->position++;

        return $character;
    }

    private function fillBuffer(): void
    {
        if ($this->buffer !== '' || $this->eof) {
            return;
        }

        $chunk = fread($this->handle, self::BUFFER_SIZE);

        if ($chunk === false || $chunk === '') {
            $this->eof = true;

            return;
        }

        $this->buffer = $chunk;
    }

    private function error(string $message): RuntimeException
    {
        return new RuntimeException($message.' Posisi byte: '.$this->position.'.');
    }
}
