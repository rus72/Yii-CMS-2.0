<?php
class LastNews extends Portlet
{
	public function renderContent() 
	{	
	    $news = News::model()->last()->active()->limit(3)->findAll();
	    if (!$news) 
	    {
	        return;
	    }    
	    
		$this->render('LastNews', array('news_list' => $news));
	}
}