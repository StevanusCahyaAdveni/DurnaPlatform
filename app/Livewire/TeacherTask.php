<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ClassGroup;
use App\Models\ClassTask;
use App\Models\ClassTaskMedia;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads; // Import trait untuk upload file
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Untuk debugging
use Illuminate\Http\Request; // Import Request untuk metode upload

class TeacherTask extends Component
{
    use WithFileUploads;

    // Properti publik untuk mengikat input form
    public $class_group_uuid;
    public $task_name;
    public $task_description; // Ini akan menerima HTML dari CKEditor
    public $task_deadline;
    public $task_media = []; // Untuk menyimpan file yang diupload via input file biasa

    public $formStatus = '0';

    // Properti untuk menampilkan pesan sukses/error
    public $message;
    public $search = '';
    public $descriptionTask = '';

    // Listener untuk event dari JavaScript (CKEditor)
    protected $listeners = [
        'ckeditorUpdate', // Listener baru untuk perubahan konten CKEditor
        'resetCkeditor' => 'resetCkeditorProperty', // Listener untuk mereset properti
    ];

    public function mount()
    {
        $this->task_name = '';
        $this->task_description = '';
        $this->task_deadline = '';
        $this->class_group_uuid = '';
        $this->formStatus = '0';
    }

    public function render()
    {
        $userId = Auth::id();

        $TaskDataQuery = ClassTask::join('class_groups', 'class_groups.id', '=', 'class_tasks.class_group_id')->select('class_tasks.*', 'class_groups.class_name')->where('class_groups.user_id', $userId);
        if ($this->search != '') {
            $TaskDataQuery->where('class_tasks.task_name', 'like', '%' . $this->search . '%');
        }
        $data = [
            'selectClass' => ClassGroup::where('user_id', $userId)->get(),
            'TaskData' => $TaskDataQuery->get(),
        ];

        return view('livewire.teacher-task', $data);
    }

    public function changeFormStatus($status)
    {
        $this->formStatus = $status;
    }

    public function changeDescription($id)
    {
        $getDescription = ClassTask::where('id', $id)->first();
        $this->descriptionTask = $getDescription->task_description;
    }

    // Metode ini akan dipanggil oleh JavaScript saat CKEditor berubah
    public function ckeditorUpdate($value)
    {
        $this->task_description = $value;
        Log::info('CKEditor content updated. Current task_description: ' . $this->task_description);
    }

    // Metode ini akan dipanggil oleh CKEditor untuk upload gambar/file
    // Ini adalah endpoint yang akan diakses oleh SimpleUploadAdapter
    public function uploadCkeditorImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');

            try {
                // Simpan file ke storage (misal: 'public/ckeditor_images')
                $path = $file->store('ckeditor_images', 'public');

                // Dapatkan URL absolut yang cocok untuk CKEditor (misal: https://domain.com/storage/ckeditor_images/xxx.jpg)
                $url = asset('storage/' . $path);

                Log::info('CKEditor image uploaded successfully: ' . $url);

                // CKEditor expects { "url": "..." }
                return response()->json(['url' => $url]);
            } catch (\Exception $e) {
                Log::error('CKEditor upload error: ' . $e->getMessage());
                return response()->json(['error' => ['message' => $e->getMessage()]], 500);
            }
        }

        return response()->json(['error' => ['message' => 'No file uploaded.']], 400);
    }

    public function inputTaskAndStoreTaskMedia()
    {
        Log::info('inputTaskAndStoreTaskMedia called. task_description: ' . $this->task_description);

        // 1. Validasi Input
        $this->validate([
            'class_group_uuid' => 'required|uuid|exists:class_groups,id',
            'task_name' => 'required|string|max:250',
            'task_description' => 'nullable|string', // CKEditor menghasilkan HTML string
            'task_deadline' => 'nullable|date_format:Y-m-d\TH:i',
            'task_media.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,zip|max:10240',
        ]);

        // 2. Buat Task Baru
        $task = ClassTask::create([
            'task_name' => $this->task_name,
            'task_description' => $this->task_description, // Simpan HTML dari CKEditor
            'task_deadline' => $this->task_deadline ? \Carbon\Carbon::parse($this->task_deadline) : null,
            'class_group_id' => $this->class_group_uuid,
        ]);

        // 3. Simpan Media (jika ada, dari input file biasa)
        if (!empty($this->task_media)) {
            foreach ($this->task_media as $mediaFile) {
                $path = $mediaFile->store('task_media', 'public');
                ClassTaskMedia::create([
                    'class_task_id' => $task->id,
                    'media_name' => basename($path),
                ]);
            }
        }

        // 4. Reset Form dan Beri Pesan Sukses
        $this->reset([
            'class_group_uuid',
            'task_name',
            'task_description',
            'task_deadline',
            'task_media',
        ]);
        $this->message = 'Tugas berhasil ditambahkan!';
        session()->flash('success_message', 'Tugas dan media berhasil ditambahkan!');

        // Dispatch event untuk mereset CKEditor di sisi klien
        $this->dispatch('resetCkeditor');
    }

    // Metode untuk mereset properti task_description
    public function resetCkeditorProperty()
    {
        $this->task_description = '';
    }

    public function deleteTask($id)
    {
        $task = ClassTask::findOrFail($id);
        $task->delete();

        // Hapus media terkait
        $taskMedia = ClassTaskMedia::where('class_task_id', $id)->get();
        foreach ($taskMedia as $media) {
            Storage::delete('public/task_media/' . $media->media_name);
        }
        ClassTaskMedia::where('class_task_id', $id)->delete();
        // unlink(public_path('storage/task_media/' . $taskMedia->media_name));
        // Storage::delete('task_media/' . $taskMedia->media_name);

        session()->flash('success_message', 'Tugas berhasil dihapus!');
    }

    public function upDataForUpdate($id){
        $getDataSingle = ClassTask::findOrFail($id);
        $this->class_group_uuid = $getDataSingle->class_group_id;
        $this->task_name = $getDataSingle->task_name;
        $this->task_description = $getDataSingle->task_description;
        $this->task_deadline = $getDataSingle->task_deadline ;
        $this->formStatus = '1'; // Ubah status form menjadi '1' untuk mode update
        $this->dispatch('resetCkeditor'); // Dispatch event untuk mereset CKEditor

    }
}
