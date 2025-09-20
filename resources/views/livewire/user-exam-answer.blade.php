<div>
    @if($layoutStatus == 'Preview')
        <div>
            <flux:heading size="xl">{{$singleDataExam->exam_name}}</flux:heading>
            <flux:badge class="mb-2" variant="pill" color="green" size="sm">{{ $singleDataExam->class_name }}</flux:badge>
            <flux:badge class="mb-2" variant="pill" icon="calendar" color="red" size="sm">{{ date('d F Y', strtotime($singleDataExam->exam_deadline)) }}</flux:badge>
            <flux:badge class="mb-2" variant="pill" icon="clock" color="blue" size="sm">{{ $time }} minutes</flux:badge><br>
            {!! $singleDataExam->exam_description !!}
            <br>
            <br>
            @if($singleDataExam->exam_deadline > now())
            <div class="flex">
                <flux:spacer></flux:spacer>
                <flux:button variant="primary" size="sm" wire:click="changeLayoutStatus('Question')">Submit Answer</flux:button>
            </div>
            @endif
        </div>
    @elseif($layoutStatus == 'Question')
        <div>
            <flux:heading size="xl">{{$singleDataExam->exam_name}}</flux:heading>
            <div class="flex">
                <flux:spacer></flux:spacer>
                <div>
                    <div x-data="{ 
                        timeLeft: '',
                        updateCountdown() {
                            const now = new Date().getTime();
                            const endTime = new Date('{{ $timeEnd }}').getTime();
                            const distance = endTime - now;
                            
                            if (distance > 0) {
                                const hours = Math.floor(distance / (1000 * 60 * 60));
                                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                
                                this.timeLeft = hours + 'h ' + minutes + 'm ' + seconds + 's';
                            } else {
                                this.timeLeft = 'Time expired';
                            }
                        }
                    }" 
                    x-init="updateCountdown(); setInterval(() => updateCountdown(), 1000)">
                        <flux:badge class="mb-2" variant="pill" icon="" color="red" size="sm" x-text="timeLeft"></flux:badge>
                        <flux:modal.trigger name="listQuestions">
                            <flux:badge variant="pill" color="blue" icon="numbered-list" size="sm">Questions</flux:badge>
                        </flux:modal.trigger>
                    </div>
                    
                    <flux:modal name="listQuestions" variant="flyout">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">Question</flux:heading>
                            </div>
                            <div class="grid grid-cols-5 gap-2">
                                @php
                                    $questionCount = 0;
                                @endphp
                                @foreach($questions as $question)
                                    @php
                                        $questionCount++;
                                        if($question->id == $singleQuestion->id){
                                            $activeClass = 'bg-blue-500 text-white';
                                        }
                                    @endphp
                                    @if($questionCount < 10)
                                        <flux:button class="" wire:click="setQuestion('{{ $question->id }}')" class="{{ $question->id == $singleQuestion->id ? 'bg-blue-500 text-white' : '' }} w-full" size="sm">0{{$questionCount}}</flux:button>
                                    @else
                                        <flux:button class="" wire:click="setQuestion('{{ $question->id }}')" class="{{ $activeClass ?? '' }} w-full" size="sm">{{$questionCount}}</flux:button>
                                    @endif
                                    
                                @endforeach
                            </div>

                            <div class="flex">
                                <flux:spacer />
                    
                                {{-- <flux:button type="submit" variant="primary">Save changes</flux:button> --}}
                            </div>
                        </div>
                    </flux:modal>
                </div>
            </div>
            <div class="mb-2 grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach ($singleQuestionMedia as $media)
                    <div>
                        <flux:modal.trigger name="imageModal{{ $media->id }}">
                            <img src="{{ asset('storage/examQuestion/' . $media->media_name) }}" alt="Question media" class="shadow-sm w-full h-20 object-cover rounded border cursor-pointer hover:opacity-80 transition-opacity">
                        </flux:modal.trigger>
                        
                        <flux:modal name="imageModal{{ $media->id }}" class="max-w-4xl">
                            <div class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <flux:heading size="lg">Question Image</flux:heading>
                                </div>
                                <div class="flex justify-center">
                                    <img src="{{ asset('storage/examQuestion/' . $media->media_name) }}" alt="Question media" class="max-w-full max-h-96 object-contain rounded">
                                </div>
                            </div>
                        </flux:modal>
                    </div>
                @endforeach
            </div>
            {!! $singleQuestion->question_text !!}
            <div class="mb-2 mt-3 space-y-2">
                @foreach ($singleQuestionOptions as $option)
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:text-black hover:bg-gray-50 transition-colors">
                        <input type="radio" wire:click="setSelectedOption('{{ $option->id }}')" @if($optionSelected->answer_text == $option->id) checked @endif name="question_option" value="{{ $option->id }}" wire:model="selectedOption" class="mr-3">
                        <span class="text-sm">{{ $option->option_text }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
