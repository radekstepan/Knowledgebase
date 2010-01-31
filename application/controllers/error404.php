<?php if (!defined('FARI')) die();

class Error404_Controller extends Fari_Controller {

	public static function _desc() { return 'Errors'; }

    public function _init() {
        $this->view->settings = Fari_Db::toKeyValues(Fari_Db::select('settings', 'name, value'), 'name');
    }

	public function index($param) {
		$this->view->display('404');
	}

}
