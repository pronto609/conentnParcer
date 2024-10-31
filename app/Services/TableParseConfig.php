<?php

namespace App\Services;

class TableParseConfig
{
    const TABLE_NAME_LIKE = 'posts';
    const CONTENT = 'post_content';
    const TITLE = 'post_title';

    const COLUMNS_PARSE = ['post_title' => false, 'post_content' => self::CUT_TAGS];

    const CUT_TAGS = ['a', 'img'];
}
