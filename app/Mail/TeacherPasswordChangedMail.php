<?php

namespace App\Mail;

use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeacherPasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Teacher $teacher;
    public string $password;

    public function __construct(Teacher $teacher, string $password)
    {
        $this->teacher = $teacher;
        $this->password = $password;
    }

    public function build()
    {
        return $this
            ->subject('Mật khẩu của bạn đã được đổi')
            ->view('emails.teacher-password-changed');
    }
}

