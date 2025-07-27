<div>
    <flux:heading size="lg">Welcome Back {{auth()->user()->name}}</flux:heading>
    <flux:text size="xs" class="text-xs text-capitalize">{{auth()->user()->email}} (<span style="text-transform: capitalize;">{{auth()->user()->role}}</span>) </flux:text>
    <div class="flex flex-wrap -mx-2  my-2">
        <div class="w-full mt-2 md:w-5/12 md:pe-2">
            <flux:callout class="h-full ">
                <flux:callout.text>Nearest Task Deadline</flux:callout.text>
                    @if($NearestTaskDeadline->count() != 0 )
                        <span style="max-height:300px; overflow-y: scroll;">
                            @foreach($NearestTaskDeadline as $task)
                                <flux:callout>
                                    <flux:heading class="mb-0">
                                        <span class="mt-0 line-clamp-1">{{ Str::limit($task->class_name, 100, '...') }}</span>
                                        <span class="mt-0 line-clamp-1">Task: {{ $task->task_name }}</span>
                                    </flux:heading>
                                    <flux:callout.text class="text-xs mt-0">Deadline {{ \Carbon\Carbon::parse($task->task_deadline)->format('d F Y H:i:s') }}</flux:callout.text>
                                </flux:callout>
                            @endforeach
                        </span>
                    @else
                        <flux:callout>
                            <center>
                                <flux:heading>Empty Data</flux:heading>
                            </center>
                        </flux:callout>
                    @endif
            </flux:callout>
        </div>
        <div class="w-full mt-2 md:w-7/12">
            <flux:callout class="h-full">
                <flux:callout.text>Average Score</flux:callout.text>
                @if($AvgScoreClass->count() != 0 )
                    <span style="max-height:300px; overflow-y: scroll;">
                        @foreach($AvgScoreClass as $class)
                            <flux:callout>
                                <flux:heading class="mb-0">
                                    <span class="mt-0 line-clamp-1">{{ Str::limit($class->class_name, 100, '...') }}</span>
                                    <span class="mt-0 line-clamp-1">Average Score: {{  $this->getAvgScoreClass($class->id) }}</span>
                                </flux:heading>
                            </flux:callout>
                        @endforeach
                    </span>
                @else
                    <flux:callout>
                        <center>
                            <flux:heading>Empty Data</flux:heading>
                        </center>
                    </flux:callout>
                @endif
            </flux:callout>
        </div>
        <div class="w-full mt-2 md:w-12/12">
            <flux:callout>
                
            </flux:callout>
        </div>
    </div>
</div>