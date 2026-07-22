<aside class="min-w-0 space-y-6 hidden xl:block lg:sticky lg:top-6 lg:self-start">
    @if ($assessmentCount > 0)
        <x-assessment::ui.card>
            <div class="space-y-4">
                <div class=" items-center justify-between gap-3 ">
                    <h2 class="text-lg font-semibold">
                        Tahap <span x-text="currentAssessmentIndex + 1"></span> dari {{ $assessmentCount }}
                    </h2>

                    <div class="text-sm font-mono font-seibold flex gap-2">
                        <p>Sisa waktu: </p>
                        <x-assessment::ui.countdown-timer :title="$countdownTitle" :target-at="$countdownTargetAt" />
                    </div>
                </div>

                @if ($assessmentCount > 0)
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-[#e2edf5]">
                        <div class="h-full rounded-full bg-[#0d5f98] transition-all duration-300"
                            x-bind:style="`width: ${progressWidth()}%`"></div>
                    </div>
                @endif

                @if (!empty($questionNavigationGroups))
                    <div class="mt-4">
                        @include('assessment.show.partials.question-navigation', [
                            'questionNavigationGroups' => $questionNavigationGroups,
                        ])
                    </div>
                @endif
            </div>
                @if ($assessmentCount > 0)
                <div class="flex flex-col gap-4 mt-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-end">
                            <x-assessment::ui.button type="button" variant="outline" icon="fas fa-arrow-left"
                                x-show="showAssessmentOverviewButton()" x-bind:disabled="isBusy()"
                                style="display: none;"
                                @click="goToAssessmentOverview()">
                                Kembali
                            </x-assessment::ui.button>

                            <x-assessment::ui.button type="button" variant="outline" class="min-w-[132px]"
                                x-show="showDraftButton()" x-bind:disabled="isBusy()"
                                x-bind:aria-busy="isSavingDraft.toString()" @click="saveDraftForCurrentStage()">
                                <span x-show="!isSavingDraft" class="inline-flex items-center">
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan Draft
                                </span>

                                <span x-show="isSavingDraft" class="inline-flex items-center" style="display: none;">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Menyimpan...
                                </span>
                            </x-assessment::ui.button>

                            <x-assessment::ui.button type="button" icon="fas fa-paper-plane"
                                x-show="showStageFinalizeButton()" x-bind:disabled="isBusy()"
                                @click="submitCurrentStage()">
                                <span x-text="currentStageFinalizeLabel()"></span>
                            </x-assessment::ui.button>


                        </div>
                    </div>
                @endif

        </x-assessment::ui.card>
    @endif
</aside>
