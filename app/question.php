<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class question extends Model
{
    //
    protected $table = "ques";
    protected $fillable = [
        'exam_type', 'subject_name', 'topic_name','question','option1','option2','option3','option4','correct_answer',
    ];
}
