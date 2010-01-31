<?php if (!defined('FARI')) die();

class Browse_Controller extends Fari_Controller {

	public static function _desc() { return 'Browse by Category or Source'; }

    public function _init() {
        $this->view->settings = Fari_Db::toKeyValues(Fari_Db::select('settings', 'name, value'), 'name');
    }

	public function index($param) {	}

	public function category($slug, $page) {
        $slug = Fari_Escape::text($slug);

        $paginator = new Fari_Paginator(5, 3);
        $this->view->paginator = $paginator->select($page, 'kb', '*', array('categorySlug' => $slug), 'date DESC');

        $this->view->title = Fari_Db::selectRow('hierarchy', 'value, slug', array('slug' => $slug, 'type' => 'category'));

        $this->view->browse = 'category';

        $this->view->display('browse');
	}

    public function source($slug, $page) {
        $slug = Fari_Escape::text($slug);

        $paginator = new Fari_Paginator(5, 3);
        $this->view->paginator = $paginator->select($page, 'kb', '*', array('sourceSlug' => $slug), 'date DESC');

        $this->view->title = Fari_Db::selectRow('hierarchy', 'value, slug', array('slug' => $slug, 'type' => 'source'));

        $this->view->browse = 'source';

        $this->view->display('browse');
	}

    public function tag($tag) {
        $tag = Fari_Escape::text($tag);

        $paginator = new Fari_Paginator(100, 3);

        switch ($tag) {
            case 'star':
                $this->view->paginator = $paginator->select(1, 'kb', '*', array('starred' => 'full'), 'date DESC');
                $this->view->title = array('value' => 'Starred');
                break;
            default: $this->redirect('/error404');
        }

        $this->view->browse = 'both';

        $this->view->display('browse');
	}

}
