<aside class="min-w-0 space-y-6 hidden xl:block lg:sticky lg:top-6 lg:self-start">
    <x-assessment::ui.card class="overflow-hidden">
        <div class=" items-center justify-between gap-3 ">
            <h2 class="text-lg font-semibold">
                Tahap <span x-text="currentAssessmentIndex + 1"></span> dari {{ $assessmentCount }}
            </h2>

            <div class="text-sm font-mono font-seibold flex gap-2">
                <p>Sisa waktu: </p>
                <x-assessment::ui.countdown-timer :title="$countdownTitle" :target-at="$countdownTargetAt" />
            </div>
        </div>

        <div class=" mt-4">
            @if ($assessmentCount > 0)
                <div class="h-2 overflow-hidden rounded-full bg-[#e2edf5]">
                    <div class="h-full rounded-full bg-[#0d5f98] transition-all duration-300"
                        x-bind:style="`width: ${progressWidth()}%`"></div>
                </div>
            @endif

            <div class="divide-y divide-slate-100">
                @foreach ($sessionDetails as $detail)
                    <x-assessment::ui.detail-row :label="$detail['label']" :value="$detail['value']" class="py-3"
                        valueClass="mt-1 font-medium leading-relaxed text-slate-900" />
                @endforeach
            </div>
        </div>

        @include('assessment.show.partials.security-status', [
            'securityPayload' => $securityPayload ?? [],
        ])

        @if ($assessmentCount > 0)
            <div class="flex flex-col gap-4 mt-2">


                <div class="flex flex-col gap-3 sm:flex-row justify-end">
                    <x-assessment::ui.button type="button" variant="outline" icon="fas fa-arrow-left"
                        x-show="!isFirstAssessment()" x-bind:disabled="isBusy()"
                        @click="goToAssessment(currentAssessmentIndex - 1)">
                        Assessment Sebelumnya
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-flag-checkered" x-show="isLastAssessment()"
                        x-bind:disabled="isBusy()" @click="openFinishModal()">
                        Selesai Assessment
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-arrow-right" x-show="!isLastAssessment()"
                        x-bind:disabled="isBusy()" @click="goToAssessment(currentAssessmentIndex + 1)">
                        Next Assessment
                    </x-assessment::ui.button>
                </div>
            </div>
        @endif

    </x-assessment::ui.card>


</aside>
