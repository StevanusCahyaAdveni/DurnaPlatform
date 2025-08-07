Buat sebuah migration pada laravel dengan table seperti ini, buat modelnya juga dan perintah pada terminalnya ya, berikut list table beserta kolomnya :

1. class_group dengan kolom :
- id use a UUID
- class_name varchar 250
- class_code varchar 10
- class_description text
- class_categori varchar 50 defaulnya public
- user_id (get from user as maker the class)
- created_at
- updated_at
- delete_at

2. class_join dengan kolom :
- id use a UUID
- classgroup_id (get from classgroup id)
- user_id (get from user as joined user)
- created_at
- updated_at
- delete_at

3. class_task dengan kolom :
- id use a UUID
- task_name varchar 250
- task_description text
- task_deadline text
- classgroup_id (get from classgroup id)
- created_at
- updated_at
- delete_at

4. class_task_media with kolom :
- id use a UUID
- task_id (get from class_task id)
- media_name varchar 250
- created_at
- updated_at
- delete_at

5. class_task_answer with kolom :
- id use a UUID
- task_id (get from class_task id)
- user_id (get from user as answer maker)
- answer_text text
- created_at
- updated_at
- delete_at

6. class_task_answer_media with kolom :
- id use a UUID
- answer_id (get from class_task_answer id)
- media_name varchar 250
- created_at
- updated_at
- delete_at

7. class_task_answer_comment with kolom :
- id use a UUID
- answer_id (get from class_task_answer id)
- user_id (get from user as comment maker)
- comment_media varchar 500
- comment_text text
- created_at
- updated_at
- delete_at

8. class_chat with kolom :
- id use a UUID
- classgroup_id (get from classgroup id)
- user_id (get from user as chat maker)
- class_chat_id (for comment default NULL or String "") varchar 50
- chat_media varchar 500
- chat_text text
- created_at
- updated_at
- delete_at

9. class_task_point with kolom :
- id use a UUID
- task_id (get from class_task id)
- answer_id (get from class_task_answer id)
- user_id (get from user as point maker)
- point varchar 50
- created_at
- updated_at
- delete_at

10. course with kolom :
- id use a UUID
- course_name varchar 250
- course_code varchar 10
- course_description text
- course_categori varchar 50 defaulnya public
- user_id (get from user as maker the course)
- price varchar 50
- course_thumbnail varchar 500
- created_at
- updated_at
- delete_at

12. course_join with kolom :
- id use a UUID
- course_id (get from course id)
- user_id (get from user as joined user)
- created_at
- updated_at
- delete_at

13. course_video with kolom :
- id use a UUID
- course_id (get from course id)
- video_title varchar 250
- video_description varchar 250
- video_name varchar 250
- video_thumbnail varchar 500
- created_at
- updated_at
- delete_at

14. subscription with kolom :
- id use a UUID
- tipe (class, course)
- class_uuid (get from class_group id)
- course_uuid (get from course id)
- user_id (get from user as subscriber)
- nominal varchar 50
- payment_method varchar 50
- expired_at datetime
- created_at
- updated_at
- delete_at

15. income with kolom :
- id use a UUID
- user_id (get from user as income maker)
- nominal varchar 50
- payment_method varchar 50
- status varchar 50 defaultnya pending
- created_at
- updated_at
- deleted_at