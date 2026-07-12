<aside class="sticky bottom-0 z-20 w-full xl:hidden">
    <x-assessment::ui.card class="overflow-hidden">
        <div class=" items-center justify-between gap-3 ">
            <h2 class="text-lg font-semibold">
                Tahap <span x-text="currentAssessmentIndex + 1"></span> dari {{ $assessmentCount }}
            </h2>

            <div class="text-sm font-mono font-seibold flex gap-2">
                <p>Sisa waktu: </p>
                <x-assessment::ui.countdown-timer :title="$countdownTitle" :target-at="$countdownTargetAt" />
            </div>
            @if ($assessmentCount > 0)
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e2edf5]">
                    <div class="h-full rounded-full bg-[#0d5f98] transition-all duration-300"
                        x-bind:style="`width: ${progressWidth()}%`"></div>
                </div>
            @endif
        </div>
        @if ($assessmentCount > 0)
            <div class="flex flex-col gap-4 mt-2">
                @include('assessment.show.partials.security-status', [
                    'securityPayload' => $securityPayload ?? [],
                ])

                @if (!empty($questionNavigationGroups))
                    <details class="overflow-hidden rounded-sm border border-[#dce8f1] bg-[#f8fbfe]">
                        <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-800">
                            Navigasi Soal
                        </summary>
                        <div class="border-t border-[#dce8f1] px-4 py-4">
                            @include('assessment.show.partials.question-navigation', [
                                'questionNavigationGroups' => $questionNavigationGroups,
                                'showTitle' => false,
                            ])
                        </div>
                    </details>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row justify-end">
                    <x-assessment::ui.button type="button" variant="outline" icon="fas fa-arrow-left"
                        x-show="canGoPreviousStage()" x-bind:disabled="isInteractionLocked()"
                        @click="goToAssessment(currentAssessmentIndex - 1)">
                        Assessment Sebelumnya
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" variant="outline" icon="fas fa-save"
                        x-show="showDraftButton()" x-bind:disabled="isBusy()"
                        @click="saveDraftForCurrentStage()">
                        Simpan Draft
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-paper-plane"
                        x-show="showStageFinalizeButton()" x-bind:disabled="isBusy()"
                        @click="submitCurrentStage()">
                        <span x-text="currentStageFinalizeLabel()"></span>
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-arrow-right" x-show="canGoNextStage()"
                        x-bind:disabled="isInteractionLocked()" @click="goToAssessment(currentAssessmentIndex + 1)">
                        Next Assessment
                    </x-assessment::ui.button>
                </div>
            </div>
        @endif

    </x-assessment::ui.card>



    <x-assessment::ui.status-badge tone="warning" class="absolute top-0 right-0 rounded-bl-sm py-2 px-5  ">
        {{ $sessionDetails[0]['value'] }}
    </x-assessment::ui.status-badge>


</aside>
