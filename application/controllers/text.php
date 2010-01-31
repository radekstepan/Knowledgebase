<?php if (!defined('FARI')) die();

class Text_Controller extends Fari_Controller {

	public static function _desc() { return 'View the KB text'; }

    public function _init() {
        $this->view->settings = Fari_Db::toKeyValues(Fari_Db::select('settings', 'name, value'), 'name');
    }

	public function index($param) {	}

	public function view($slug, $highlight) {
        $slug = Fari_Escape::text($slug);

        $result = Fari_Db::selectRow('kb', '*', array('slug' => $slug));
        if (empty($result)) {
            // text not found
            $this->redirect('/error404'); die();
        }

        // highlight keywords in the text
        if (!empty($highlight)) {
            $highlight = explode('-', $highlight);
            $this->view->text = Fari_Format::highlight($result, $highlight,
                array('tags', 'source', 'category', 'comments', 'text', 'title'));
        } else $this->view->text = $result;

        $this->view->display('view');
	}

    public function edit($slug) {
        $slug = Fari_Escape::text($slug);

        // are we saving?
        if ($_POST) {
            $success = TRUE;
            // save categories, sources & types
            $category = Fari_Escape::text($_POST['category']); $categorySlug = Fari_Escape::slug($category);
            $source = Fari_Escape::text($_POST['source']); $sourceSlug = Fari_Escape::slug($source);
            $type = Fari_Escape::text($_POST['type']); $typeSlug = Fari_Escape::slug($type);

            if (empty($category)) { Fari_Message::fail('The category can\'t be empty.'); $success = FALSE; }
            else {
                $result = Fari_Db::selectRow('hierarchy', 'key', array('value' => $category, 'type' => 'category'));
                if (empty($result)) Fari_Db::insert('hierarchy', array('value' => $category, 'slug' => $categorySlug,
                        'type' => 'category'));
            }
            if (empty($source)) { Fari_Message::fail('The source can\'t be empty.'); $success = FALSE; }
            else {
                $result = Fari_Db::selectRow('hierarchy', 'key', array('value' => $source, 'type' => 'source'));
                if (empty($result)) Fari_Db::insert('hierarchy', array('value' => $source, 'slug' => $sourceSlug,
                        'type' => 'source'));
            }
            if (empty($type)) { Fari_Message::fail('The category can\'t be empty.'); $success = FALSE; }
            else {
                $result = Fari_Db::selectRow('hierarchy', 'key', array('value' => $type, 'type' => 'type'));
                if (empty($result)) Fari_Db::insert('hierarchy', array('value' => $type, 'type' => 'type'));
            }

            if ($success) {
                $text = Fari_Escape::quotes($_POST['textarea']);
                // convert main text to stems & add the lowercase original to it (better matches)
                $stems = Knowledge::stems($text) . ' ' . strtolower($text);

                $tags = Fari_Escape::text($_POST['tags']);
                $category = Fari_Escape::text($_POST['category']);
                $source = Fari_Escape::text($_POST['source']);
                $type = Fari_Escape::text($_POST['type']);
                $comments = Fari_Escape::text($_POST['comments']);
                $date = Fari_Escape::text($_POST['date']);

                // date
                if (!Fari_Filter::isDate($date)) {
                    Fari_Message::fail('The date is not in the correct format.');
                } else {
                    // INSERT
                    Fari_Db::update('kb', array('text' => $text, 'comments' => $comments, 'date' => $date,
                        'tags' => $tags, 'category' => $category, 'categorySlug' => $categorySlug,
                        'source' => $source, 'sourceSlug' => $sourceSlug, 'type' => $type, 'stems' => $stems),
                        array('slug' => $slug));
                    Fari_Message::success('Saved successfully.');
                }
            }
        }

        // fetch categories, sources & types
        $this->view->categories = $categories =
            Fari_Db::select('hierarchy', 'key, value', array('type' => 'category'), 'slug ASC');
        $this->view->sources = $sources =
            Fari_Db::select('hierarchy', 'key, value', array('type' => 'source'), 'slug ASC');
        $this->view->types = $types =
            Fari_Db::select('hierarchy', 'key, value', array('type' => 'type'), 'value ASC');

        // form
        $saved = Fari_Db::selectRow('kb', '*', array('slug' => $slug));
        $saved['textarea'] = $saved['text']; // for reuse...
        $this->view->saved = $saved;

        // get all messages
        $this->view->messages = Fari_Message::get();

        $this->view->display('edit');
	}

    public function star($slug) {
        $result = Fari_Db::selectRow('kb', '*', array('slug' => $slug));
        if (empty($result)) {
            // text not found
            $this->redirect('/error404'); die();
        }

        // switch the star for the text we have already fetched & update in the db
        if ($result['starred'] == 'full') {
            $result['starred'] = 'empty'; // switch in the current set
            Fari_Db::update('kb', array('starred' => 'empty'), array('id' => $result['id']));
        } else {
            $result['starred'] = 'full'; // switch in the current set
            Fari_Db::update('kb', array('starred' => 'full'), array('id' => $result['id']));
        }

        // return back
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

}
