@page { size: A4 landscape; margin: 0; }

.certificate-page,
.certificate-page * { box-sizing: border-box; }

.certificate-page {
    position: relative;
    width: 297mm;
    height: 210mm;
    overflow: hidden;
    color: #111;
    font-family: DejaVu Sans, sans-serif;
    text-align: center;
}

.certificate-background {
    position: absolute;
    inset: 0;
    width: 297mm;
    height: 210mm;
}

.certificate-top-logos {
    position: absolute;
    top: 9mm;
    right: 18mm;
    width: 43.5mm;
    height: 13mm;
}

.certificate-top-logo-quality {
    position: absolute;
    top: 1.6mm;
    left: 0;
    display: block;
    width: 22mm;
    height: auto;
}

.certificate-top-logo-ramah {
    position: absolute;
    top: 1.5mm;
    right: 0;
    display: block;
    width: 19mm;
    height: auto;
}

.certificate-content {
    position: absolute;
    top: 9mm;
    right: 30mm;
    bottom: 10mm;
    left: 30mm;
}

.certificate-logo {
    display: block;
    width: 19mm;
    height: 19mm;
    margin: 0 auto 1mm;
}

.certificate-institution {
    font-size: 14pt;
    font-weight: 700;
    line-height: 1.12;
}

.certificate-title {
    margin-top: 7mm;
    font-size: 25pt;
    font-weight: 700;
    letter-spacing: .4mm;
}

.certificate-number {
    margin-top: 1mm;
    font-size: 11pt;
}

.certificate-recipient {
    margin: 7mm 0 2mm;
    font-size: 12pt;
}

.certificate-name {
    display: inline-block;
    border-bottom: 1px solid #111;
    font-size: 11pt;
    font-weight: 700;
}

.certificate-institution-name {
    margin-top: 3mm;
    font-size: 12pt;
}

.certificate-description {
    max-width: 220mm;
    margin: 5mm auto 0;
    font-size: 11.5pt;
    line-height: 1.35;
}

.certificate-activity {
    font-size: 12.5pt;
    font-weight: 700;
}

.certificate-page--description-compact .certificate-description {
    margin-top: 3mm;
    font-size: 10pt;
    line-height: 1.25;
}

.certificate-page--description-compact .certificate-activity {
    font-size: 11pt;
}

.certificate-page--description-dense .certificate-description {
    margin-top: 2mm;
    font-size: 8.5pt;
    line-height: 1.15;
}

.certificate-page--description-dense .certificate-activity {
    font-size: 9.5pt;
}

.certificate-page--description-extreme .certificate-description {
    margin-top: 0;
    font-size: 6.5pt;
    line-height: 1.1;
}

.certificate-page--description-extreme .certificate-activity {
    font-size: 7.5pt;
}

.certificate-page--description-extreme .certificate-title {
    margin-top: 4mm;
}

.certificate-page--description-extreme .certificate-recipient {
    margin-top: 3.5mm;
}

.certificate-page--description-extreme .certificate-institution-name {
    margin-top: 1.5mm;
}

.certificate-signature {
    position: absolute;
    right: 0;
    top: 142mm;
    left: 0;
    font-size: 11pt;
    line-height: 1.15;
    z-index: 2;
}

.certificate-qr-wrap {
    height: 26mm;
    margin: 1.5mm auto 0;
}

.certificate-qr {
    display: block;
    width: 24mm;
    height: 24mm;
    margin: 0 auto;
}

body.certificate-pdf .certificate-qr-wrap,
.certificate-preview-viewport .certificate-qr-wrap {
    height: 24mm;
    margin: .5mm auto 0;
    line-height: 0;
}

.certificate-signature-space {
    height: 17mm;
}

.certificate-signature-name {
    font-size: 12pt;
    font-weight: 700;
}

.certificate-materials-page {
    position: relative;
    width: 297mm;
    height: 210mm;
    overflow: hidden;
    color: #111;
    font-family: DejaVu Sans, sans-serif;
    text-align: center;
    page-break-before: always;
}

.certificate-materials-background {
    position: absolute;
    inset: 0;
    width: 297mm;
    height: 210mm;
}

.certificate-materials-title {
    position: relative;
    z-index: 1;
    padding: 17mm 20mm 0;
    font-size: 16pt;
    font-weight: 700;
}

.certificate-materials-table {
    position: relative;
    z-index: 1;
    width: 245mm;
    margin: 18mm 26mm 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
    font-family: "Times New Roman", Times, serif;
    font-size: 12pt;
    font-size: var(--certificate-materials-font-size, 12pt);
    text-align: left;
    page-break-inside: avoid;
    break-inside: avoid;
}

.certificate-materials-table th,
.certificate-materials-table td {
    border-top: .25mm solid #1f2933;
    border-left: .25mm solid #1f2933;
    padding: 3.1mm 2.5mm;
    padding: var(--certificate-materials-cell-padding-y, 3.1mm) 2.5mm;
    vertical-align: middle;
}

.certificate-materials-page--tight .certificate-materials-table {
    font-size: 10pt;
    font-size: var(--certificate-materials-font-size, 10pt);
}

.certificate-materials-page--tight .certificate-materials-table th,
.certificate-materials-page--tight .certificate-materials-table td {
    padding: 2.1mm 2.5mm;
    padding: var(--certificate-materials-cell-padding-y, 2.1mm) 2.5mm;
}

.certificate-materials-page--compact .certificate-materials-table {
    font-size: 8pt;
    font-size: var(--certificate-materials-font-size, 8pt);
}

.certificate-materials-page--compact .certificate-materials-table th,
.certificate-materials-page--compact .certificate-materials-table td {
    padding: 1.5mm 2.5mm;
    padding: var(--certificate-materials-cell-padding-y, 1.5mm) 2.5mm;
}

.certificate-materials-page--dense .certificate-materials-table {
    font-size: 6pt;
    font-size: var(--certificate-materials-font-size, 6pt);
}

.certificate-materials-page--dense .certificate-materials-table th,
.certificate-materials-page--dense .certificate-materials-table td {
    padding: .9mm 2.5mm;
    padding: var(--certificate-materials-cell-padding-y, .9mm) 2.5mm;
}

.certificate-materials-page--ultra-dense .certificate-materials-table {
    font-size: 5pt;
    font-size: var(--certificate-materials-font-size, 5pt);
}

.certificate-materials-page--ultra-dense .certificate-materials-table th,
.certificate-materials-page--ultra-dense .certificate-materials-table td {
    padding: .4mm 2.5mm;
    padding: var(--certificate-materials-cell-padding-y, .4mm) 2.5mm;
}

.certificate-materials-page--extreme-dense .certificate-materials-table {
    font-size: 4pt;
    font-size: var(--certificate-materials-font-size, 4pt);
}

.certificate-materials-page--extreme-dense .certificate-materials-table th,
.certificate-materials-page--extreme-dense .certificate-materials-table td {
    padding: .2mm 2.5mm;
    padding: var(--certificate-materials-cell-padding-y, .2mm) 2.5mm;
}

.certificate-materials-table tr {
    page-break-inside: avoid;
    break-inside: avoid;
}

.certificate-materials-table tr > :last-child {
    border-right: .25mm solid #1f2933;
}

.certificate-materials-table tr:last-child > * {
    border-bottom: .25mm solid #1f2933;
}

.certificate-materials-table thead th {
    background: #0f3b67;
    color: #fff;
    font-weight: 700;
    text-align: center;
}

.certificate-materials-table th:first-child,
.certificate-materials-table td:first-child {
    width: 18mm;
    text-align: center;
}

.certificate-materials-table th:last-child,
.certificate-materials-table td:last-child {
    width: 23mm;
    text-align: center;
}

.certificate-materials-table tfoot td {
    background: #b9cbed;
    font-weight: 700;
    text-align: center;
}

.certificate-materials-empty {
    color: #6b7280;
    text-align: center;
}
