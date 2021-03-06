<?php

namespace Model;

class Post extends Base {

	// data configuration
	protected
		$fieldConf = array(
			'title' => array(
				'type' => \DB\SQL\Schema::DT_VARCHAR256,
				'nullable'=>false,
				'required'=>true,
			),
			'slug' => array(
				'type' => \DB\SQL\Schema::DT_VARCHAR256,
				'nullable'=>false,
				'required' => true,
			),
			'image' => array(
				'type' => \DB\SQL\Schema::DT_VARCHAR256,
			),
			'teaser' => array(
				'type' => \DB\SQL\Schema::DT_TEXT,
			),
			'text' => array(
				'type' => \DB\SQL\Schema::DT_TEXT,
				'required' => true,
			),
			'publish_date' => array(
				'type' => \DB\SQL\Schema::DT_TIMESTAMP
			),
			'published' => array(
				'type' => \DB\SQL\Schema::DT_BOOLEAN,
				'default'=>0,
			),
			'author' => array(
				'belongs-to-one' => '\Model\User',
			),
			'tags' => array(
				'has-many' => array('\Model\Tag','post'),
			),
			'comments' => array(
				'has-many' => array('\Model\Comment','post'),
			),
			'enable_comments' => array(
				'type' => \DB\SQL\Schema::DT_BOOLEAN,
				'default'=>1,
			),
		),
		$table = 'posts',
		$db = 'DB';

	/**
	 * magic setter for publish_date
	 */
	public function set_publish_date($val) {
		// make input date compatible with DB datatype format
		return date('Y-m-d',strtotime($val));
	}

	/**
	 * magic setter for title
	 */
	public function set_title($val) {
		// auto create slug when setting a blog title
		$this->set('slug',\Web::instance()->slug($val));
		return $val;
	}

	/**
	 * set and add new tags to the post entity
	 * @param $val
	 * @return array
	 */
	public function set_tags($val) {
		if (!empty($val)) {
			$tagsArr = \Base::instance()->split($val);
			$tag_res = new Tag();
			// find IDs of known Tags
			$known_tags = $tag_res->find(array('title IN ?', $tagsArr));
			if ($known_tags) {
				foreach ($known_tags as $tag)
					$tags[$tag->_id] = $tag->title;
				$newTags = array_diff($tagsArr, array_values($tags));
			} else
				$newTags = $tagsArr;
			// create remaining new Tags
			foreach ($newTags as $tag) {
				$tag_res->reset();
				$tag_res->title = $tag;
				$out = $tag_res->save();
				$tags[$out->_id] = $out->title;
			}
			// set array of IDs to current Post
			$val = array_keys($tags);
		}
		return $val;
	}


	public function save()
	{
		/** @var Base $f3 */
		$f3 = \Base::instance();
		if(!$this->author)
			$this->author = $f3->get('BACKEND_USER')->_id;
		return parent::save();
	}


}