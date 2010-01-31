<?php if (!defined('FARI')) die();

class Settings_Controller extends Fari_Controller {

	public static function _desc() { return 'Settings'; }

	public function index($param) {
        // get installed CSS themes
        $files = Fari_File::listing('/public'); $themes = array();
        foreach ($files as $file) {
            $css = end(explode('/', $file['path'])); // its cheap
            if ($file['type'] == 'file' && substr($css, -4) == '.css') $themes []= substr($css, 0, -4);
        }
        natsort(&$themes);
        $this->view->themes = $themes;

		// are we saving changes?
        if ($_POST) {
            $css = Fari_Escape::text($_POST['css']); $title = Fari_Escape::text($_POST['title']);
            Fari_Db::update('settings', array('value' => $css), array('name' => 'theme'));
            Fari_Db::update('settings', array('value' => $title), array('name' => 'title'));
            Fari_Message::success('Settings change successful.');
        }

        $this->view->messages = Fari_Message::get();

        $this->view->settings = Fari_Db::toKeyValues(Fari_Db::select('settings', 'name, value'), 'name');
        $this->view->display('settings');
	}

}
