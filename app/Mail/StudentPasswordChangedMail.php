<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentPasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Student $student;
    public string $password;

    public function __construct(Student $student, string $password)
    {
        $this->student = $student;
        $this->password = $password;
    }

    public function build()
    {
        return $this
            ->subject('Mật khẩu của bạn đã được đổi')
            ->view('emails.student-password-changed');
    }
}

