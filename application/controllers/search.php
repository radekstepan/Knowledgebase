<?php if (!defined('FARI')) die();

class Search_Controller extends Fari_Controller {

	public static function _desc() { return 'Knowledgebase Core'; }

    public function _init() {
        $this->view->settings = Fari_Db::toKeyValues(Fari_Db::select('settings', 'name, value'), 'name');
    }

	public function index($param) {
        // fetch categories & sources
        $this->view->categories = Fari_Db::select('hierarchy', 'value, slug', array('type' => 'category'), 'slug ASC');
        $this->view->sources = Fari_Db::select('hierarchy', 'value, slug', array('type' => 'source'), 'slug ASC');

		$this->view->display('search');
	}

    public function results($query) {
        if (!empty($query)) {
            // cleanup, convert, replace, strip...
            $query = Fari_Decode::url($query);
            $query = preg_replace('~\s{2,}~', ' ', implode(' ', explode('-', strtolower($query))));
            $query = (substr($query, -1) == ' ') ? substr($query, 0, -1) : $query; // trailing space
            $query = (substr($query, 0, 1) == ' ') ? substr($query, 1) : $query; // leading space
            $this->view->query = $query = Fari_Escape::alpha($query);
            $this->view->keywords = implode('-', explode(' ', $query)); // implode back to have clean keywords
        } else { $this->redirect('/'); die(); }

        // fetch the result and add relevance to it
        $this->view->result = Search::query($query);

        $this->view->display('results');
	}

}
