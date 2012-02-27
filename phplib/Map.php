<?php

class Map {

	public static $map =
		array(
	    // main
	    '/' => 'main.xml',
	    // misc
	    404 => 'errors/p404.xml',
	    502 => 'errors/p502.xml',
	    //ratings
	    'visits' => 'rating/visits.xml',
	    'links' => 'rating/links.xml',
	    'comments' => 'rating/comments.xml',
	    'visits' => 'rating/visits.xml',
	    //posts
	    'post/%d/%d' => 'posts/item.xml'
	);
	public static $sinonim =
		array(
	    'synthesis' => '/',
	);

}
