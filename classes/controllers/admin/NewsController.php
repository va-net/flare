<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class NewsController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->news = News::get();
        $this->render('admin/news', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        switch (Input::get('action')) {
            case 'deletearticle':
                News::archive(Input::get('delete'));
                Session::flash('success', 'News Item Archived Successfully! ');
                $this->redirect('/admin/news');
                break;
            case 'editarticle':
                News::edit(Input::get('id'), [
                    'subject' => Input::get('title'),
                    'content' => Input::get('content'),
                ]);
                Session::flash('success', 'News Updated');
                $this->redirect('/admin/news');
                break;
            case 'newarticle':
                News::add([
                    'subject' => Input::get('title'),
                    'content' => Input::get('content'),
                    'author' => Input::get('author'),
                ]);
                Session::flash('success', 'News Added');
                $this->redirect('/admin/news');
                break;
            default:
                $this->get();
                break;
        }
    }
}
