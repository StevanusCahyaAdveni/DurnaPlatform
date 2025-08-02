<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ClassGroup;
use App\Models\ClassJoin;
use App\Models\ClassChat;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassTask; // Assuming you have a ClassTask model for tasks
use Illuminate\Support\Str; // For string helpers like Str::uuid() if needed

class UserClassDetail extends Component
{
    public $classGroupId;
    public $newMessage = ''; // Initialize as empty
    public $singleClass; // To store class detail data
    public $taskSearch = ''; // To store task search query
    public $perPage = 20; // Number of chats displayed initially and when loading more
    public $allMessagesLoaded = false; // Flag to indicate if all messages have been loaded
    public $isLoadingMore = false; // Flag to show loading indicator

    public $layoutStatus = 'tasks';
    protected $listeners = ['messageSent', 'loadMore'];
    public $searchPeople = '';

    public function mount($id)
    {
        $this->classGroupId = $id;

        // Access Validation: Move to mount() so it's executed only once
        $teacherCheck = ClassGroup::where('id', $this->classGroupId)->where('user_id', Auth::id())->exists();
        $studentCheck = ClassJoin::where('class_group_id', $this->classGroupId)->where('user_id', Auth::id())->exists();

        if (!$teacherCheck && !$studentCheck) {
            // Use redirect() without navigate:true if you don't want Livewire navigation
            // Or use $this->redirect(route('our-class'), navigate: true); if you want Livewire navigation
            return $this->redirect(route('our-class'), navigate: true);
        }

        // Fetch class data once in mount
        $this->singleClass = ClassGroup::find($this->classGroupId);

        // If class not found after validation (though it shouldn't happen)
        if (!$this->singleClass) {
            return $this->redirect(route('our-class'), navigate: true);
        }
    }

    public function render()
    {
        // Get total number of messages for this class
        $totalChatCount = ClassChat::where('class_group_id', $this->classGroupId)->count();

        // Fetch chats with eager loading for the user relation
        // Order by created_at in descending order to get the latest messages
        // Then use take() to limit the number of messages displayed
        $classChat = ClassChat::with('user')
            ->where('class_group_id', $this->classGroupId)
            ->orderBy('created_at', 'desc') // Get the newest first
            ->take($this->perPage)
            ->get()
            ->reverse(); // Reverse the order so the newest are at the bottom

        $classTaskQuery = ClassTask::select('class_tasks.*', 'users.name', 'class_groups.class_name')->join('class_groups', 'class_tasks.class_group_id', '=', 'class_groups.id')->join('users', 'class_groups.user_id', '=', 'users.id')->where('class_groups.id', $this->classGroupId);

        if ($this->taskSearch != '') {
            $classTaskQuery->where('class_tasks.task_name', 'like', '%' . $this->taskSearch . '%');
        }

        $classTask= $classTaskQuery->orderBy('class_tasks.created_at', 'desc')->get();

        // Check if all messages have been loaded
        $this->allMessagesLoaded = ($this->perPage >= $totalChatCount);
        $this->isLoadingMore = false; // Reset loading state after render

        $classPeople = ClassJoin::join('users', 'class_joins.user_id', '=', 'users.id')->join('class_groups', 'class_joins.class_group_id', '=', 'class_groups.id')->select('class_joins.*', 'users.name', 'users.email', 'users.role', 'class_groups.user_id as owner')->where('class_group_id', $this->classGroupId);
        if ($this->searchPeople != '') {
            $classPeople->where('users.name', 'like', '%' . $this->searchPeople . '%');
        }
        $classPeople = $classPeople->get();

        return view('livewire.user-class-detail', [
            'singleClass' => $this->singleClass, // Use the already initialized property
            'classChat' => $classChat,
            'classTask' => $classTask,
            'classPeople' => $classPeople,
        ]);
    }

    public function sendMessage()
    {
        // Validate message input
        $this->validate([
            'newMessage' => 'required', // Adjust validation rules
        ]);

        ClassChat::create([
            'class_group_id' => $this->classGroupId,
            'user_id' => Auth::id(), // Use Auth::id() to get the user's UUID
            'chat_text' => $this->newMessage,
            // class_chat_id and chat_media can be filled if there are reply/media features
            // 'class_chat_id' => null, // Default null for main messages
            // 'chat_media' => null,
        ]);

        $this->newMessage = ''; // Clear input after message is sent

        // After sending a message, ensure we display all latest messages
        // This will trigger a re-render and scroll to the bottom
        $this->perPage = max($this->perPage, ClassChat::where('class_group_id', $this->classGroupId)->count());

        // Dispatch event to tell JavaScript to scroll to the bottom
        $this->dispatch('messageSent');
    }

    // This method will be called from JavaScript (Alpine.js) when scrolling up
    public function loadMore()
    {
        if ($this->allMessagesLoaded) {
            return; // Don't load more if all messages are already loaded
        }

        $this->isLoadingMore = true; // Set loading state
        $this->perPage += 20; // Add more messages to load (e.g., 20 more messages)
        // Livewire will automatically call render() again after the $perPage property changes
        // and then will trigger a scroll to the correct position in the Blade view
    }

    public function changeLayoutStatus($status)
    {
        $this->layoutStatus = $status;
    }

    public function deletePeople($id)
    {
        // Validate the user has permission to delete this person
        $classJoin = ClassJoin::where('id', $id)->where('class_group_id', $this->classGroupId)->first();
        $classJoin->delete();
        session()->flash('message', 'Person removed successfully.');
    }
}
