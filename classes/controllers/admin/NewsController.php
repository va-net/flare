<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class NewsController extends Controller
{
    public function get_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->news = News::get();
        $data->active_dropdown = 'site-management';
        $this->render('admin/news', $data);
    }

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        switch (Input::get('action')) {
            case 'deletearticle':
                $this->delete();
                break;
            case 'editarticle':
                $this->update();
                break;
            case 'newarticle':
                $this->add();
                break;
        }

        $this->get_index();
    }

    public function get_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        $data = new stdClass;
        $data->user = $user;
        $data->active_dropdown = 'site-management';

        $this->render('admin/news_create', $data);
    }

    public function post_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');

        $this->add();
        $this->redirect('/admin/news');
    }

    public function get_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        $data = new stdClass;
        $data->user = $user;

        $data->article = News::find($id);
        $data->active_dropdown = 'site-management';
        $this->render('admin/news_edit', $data);
    }

    public function post_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'site');

        $this->update($id);
        $this->get_edit($id);
    }

    private function delete()
    {
        News::archive(Input::get('delete'));
        Session::flash('success', 'News Item Archived Successfully!');
    }

    private function add()
    {
        News::add([
            'subject' => Input::get('title'),
            'content' => Input::get('content'),
            'author' => (new User)->data()->name,
        ]);
        Session::flash('success', 'News Added');
    }

    private function update()
    {
        News::edit(Input::get('id'), [
            'subject' => Input::get('title'),
            'content' => Input::get('content'),
        ]);
        Session::flash('success', 'News Updated');
    }
}
