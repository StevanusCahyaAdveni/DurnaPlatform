# Exam System - Migrations dan Models

## Overview

Sistem exam telah dibuat dengan 7 table dan model yang saling berelasi untuk mendukung fitur ujian/exam dalam platform Durna.

## Database Schema

### 1. Table `exams`

**Migration:** `2025_08_08_155301_create_exams_table.php`
**Model:** `App\Models\Exam`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `exam_name` (VARCHAR 250)
-   `exam_description` (TEXT)
-   `exam_deadline` (DATETIME)
-   `classgroup_id` (UUID, Foreign Key ke class_groups)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` ClassGroup
-   `hasMany` ExamQuestion
-   `hasMany` ExamAnswer

### 2. Table `exam_questions`

**Migration:** `2025_08_08_155324_create_exam_questions_table.php`
**Model:** `App\Models\ExamQuestion`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `exam_id` (UUID, Foreign Key ke exams)
-   `question_text` (TEXT)
-   `question_type` (VARCHAR 50)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` Exam
-   `hasMany` ExamQuestionOption
-   `hasMany` ExamQuestionMedia
-   `hasMany` ExamQuestionAnswer

### 3. Table `exam_question_options`

**Migration:** `2025_08_08_155332_create_exam_question_options_table.php`
**Model:** `App\Models\ExamQuestionOption`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `question_id` (UUID, Foreign Key ke exam_questions)
-   `option_text` (TEXT)
-   `is_correct` (BOOLEAN, Default: false)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` ExamQuestion
-   `hasMany` ExamQuestionOptionMedia

### 4. Table `exam_question_media`

**Migration:** `2025_08_08_155340_create_exam_question_media_table.php`
**Model:** `App\Models\ExamQuestionMedia`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `question_id` (UUID, Foreign Key ke exam_questions)
-   `media_name` (VARCHAR 250)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` ExamQuestion

### 5. Table `exam_question_option_media`

**Migration:** `2025_08_08_155347_create_exam_question_option_media_table.php`
**Model:** `App\Models\ExamQuestionOptionMedia`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `option_id` (UUID, Foreign Key ke exam_question_options)
-   `media_name` (VARCHAR 250)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` ExamQuestionOption

### 6. Table `exam_answers`

**Migration:** `2025_08_08_155357_create_exam_answers_table.php`
**Model:** `App\Models\ExamAnswer`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `exam_id` (UUID, Foreign Key ke exams)
-   `user_id` (UUID, Foreign Key ke users)
-   `point` (VARCHAR 50)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` Exam
-   `belongsTo` User
-   `hasMany` ExamQuestionAnswer

### 7. Table `exam_question_answers`

**Migration:** `2025_08_08_155408_create_exam_question_answers_table.php`
**Model:** `App\Models\ExamQuestionAnswer`

**Kolom:**

-   `id` (UUID, Primary Key)
-   `exam_answer_id` (UUID, Foreign Key ke exam_answers)
-   `question_id` (UUID, Foreign Key ke exam_questions)
-   `user_id` (UUID, Foreign Key ke users)
-   `answer_text` (TEXT)
-   `created_at` (TIMESTAMP)
-   `updated_at` (TIMESTAMP)
-   `deleted_at` (TIMESTAMP, Soft Delete)

**Relasi:**

-   `belongsTo` ExamAnswer
-   `belongsTo` ExamQuestion
-   `belongsTo` User

## Fitur Model

### UUID Primary Keys

Semua model menggunakan UUID sebagai primary key dengan auto-generate UUID saat create.

### Soft Deletes

Semua model menggunakan soft delete untuk menjaga integritas data.

### Relationships

Semua model sudah dilengkapi dengan relationship methods untuk memudahkan query.

### Fillable Properties

Setiap model sudah memiliki `$fillable` array untuk mass assignment protection.

## Perintah Terminal yang Digunakan

```bash
# Membuat Migrations
php artisan make:migration create_exams_table
php artisan make:migration create_exam_questions_table
php artisan make:migration create_exam_question_options_table
php artisan make:migration create_exam_question_media_table
php artisan make:migration create_exam_question_option_media_table
php artisan make:migration create_exam_answers_table
php artisan make:migration create_exam_question_answers_table

# Membuat Models
php artisan make:model Exam
php artisan make:model ExamQuestion
php artisan make:model ExamQuestionOption
php artisan make:model ExamQuestionMedia
php artisan make:model ExamQuestionOptionMedia
php artisan make:model ExamAnswer
php artisan make:model ExamQuestionAnswer

# Menjalankan Migrations
php artisan migrate
```

## Contoh Penggunaan

### Membuat Exam

```php
$exam = Exam::create([
    'exam_name' => 'Ujian Matematika',
    'exam_description' => 'Ujian tengah semester matematika',
    'exam_deadline' => '2025-08-15 23:59:59',
    'classgroup_id' => $classGroupId,
]);
```

### Membuat Question dengan Options

```php
$question = ExamQuestion::create([
    'exam_id' => $exam->id,
    'question_text' => 'Berapa hasil dari 2 + 2?',
    'question_type' => 'multiple_choice',
]);

// Options
$question->options()->createMany([
    ['option_text' => '3', 'is_correct' => false],
    ['option_text' => '4', 'is_correct' => true],
    ['option_text' => '5', 'is_correct' => false],
]);
```

### Query dengan Relasi

```php
// Get exam dengan questions dan options
$exam = Exam::with(['questions.options', 'classGroup'])
    ->find($examId);

// Get user answers untuk exam tertentu
$userAnswers = ExamAnswer::with(['questionAnswers.question'])
    ->where('exam_id', $examId)
    ->where('user_id', $userId)
    ->first();
```

## Status

✅ **COMPLETED** - Semua table dan model exam system berhasil dibuat dan dijalankan.
