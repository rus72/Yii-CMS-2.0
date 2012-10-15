<?php

class ValetudoParser extends OsParser
{
    public function getSource()
    {
        return 'valetudo.ru';
    }


    public function getWebUrl()
    {
        return 'http://valetudo.ru/';
    }


    public function getAuthorId()
    {
        return 555;
    }


    public function parsePosts()
    {
        $content = $this->getContent($this->web_url);
        if (!$content)
        {
            return;
        }

        $doc = new DOMDocument();
        $doc->validateOnParse = false;
        @$doc->loadHTML($content);

        $xpath  = new DOMXPath($doc);
        $links  = $xpath->query('//a[@class="contentpagetitle"]');
        $images = $xpath->query('//div[@class="contentpaneopen"]//img');

        foreach ($links as $i => $link)
        {
            $href = $source_url = $link->getAttribute('href');
            $page = Page::model()->findByAttributes(array(
                'source'    => $this->source,
                'source_url' => $source_url
            ));

            if ($page)
            {
                $this->log("Пост #{$page->id} уже был спарсен, пропускаем");
                continue;
            }

            $title = trim($link->textContent);

            $content = $this->getContent($this->web_url . $href);
            if (!$content)
            {
                continue;
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($content);

            $path = new DOMXPath($doc);

            $text = $path->query("//div[@class='article-content']");
            $text = $doc->saveXML($text->item(0));
            $text = $this->stripTags($text);
            $text = preg_replace('|Комментарий \[[0-9]+\]|', '', $text);

            $image = $images->item($i);
            if ($image)
            {
                $image = $image->getAttribute('src');
            }

            if ($image)
            {
                $this->log("Не смог спарсить картинку для: {$href}");
            }

            $page = new Page();
            $page->source     = $this->source;
            $page->source_url = $source_url;
            $page->user_id    = $this->author_id;
            $page->title      = $title;
            $page->text       = $text;
            $page->image      = $image;
            $page->status     = Page::STATUS_PUBLISHED;
            $page->type       = Page::TYPE_POST;

            $this->saveModel($page);
        }
    }
}



















