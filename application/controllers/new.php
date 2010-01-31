<?php if (!defined('FARI')) die();

class New_Controller extends Fari_Controller {

	public static function _desc() { return 'Adding new knowledge'; }

    public function _init() {
        $this->view->settings = Fari_Db::toKeyValues(Fari_Db::select('settings', 'name, value'), 'name');
    }

	public function index($param) {
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
                $title = Fari_Escape::text($_POST['title']);
                if (empty($title)) {
                    Fari_Message::fail('The title can\'t be empty.');
                } else {
                    $slug = Fari_Escape::slug($_POST['title']);

                    // unique slug/title
                    $result = Fari_Db::selectRow('kb', 'id', array('slug' => $slug));
                    if (!empty($result)) {
                        Fari_Message::fail('The title is not unique.');
                    } else {
                        $text = Fari_Escape::quotes($_POST['textarea']);
                        // convert title & main text to its stems and add lowercase originals better matches)
                        $titleStems = Knowledge::stems($title) . ' ' . strtolower($title);
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
                            Fari_Db::insert('kb', array('title' => $title, 'slug' => $slug, 'text' => $text,
                                'tags' => $tags, 'category' => $category, 'categorySlug' => $categorySlug,
                                'source' => $source, 'sourceSlug' => $sourceSlug, 'type' => $type, 'stems' => $stems,
                                'comments' => $comments, 'date' => $date, 'titleStems' => $titleStems,
                                'starred' => 'empty'));
                            Fari_Message::success('Saved successfully.');
                            $this->redirect('/text/edit/' . $slug); die();
                        }
                    }
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

        // form if save failed...
        $this->view->saved = $_POST;
        // get all messages
        $this->view->messages = Fari_Message::get();

        $this->view->display('new');
	}

}
