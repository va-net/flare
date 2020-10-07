<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('News Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('newsmanage') || !$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'site-management';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include '../includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include '../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include '../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                        }
                        ?>
                        <h3>Manage News</h3>
                        <h4>Active News Articles</h4>
                        <div class="modal fade" id="confirmNewsDelete">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title">Are You Sure?</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <div class="modal-body">
                                Are you sure you want to delete this News Item?
                                <form id="deletearticle" action="/update.php" method="post">
                                    <input hidden name="action" value="deletearticle" />
                                    <input hidden name="delete" id="confirmNewsDelete-id" />
                                    <input type="submit" class="btn btn-danger" value="Delete" />
                                </form>
                            </div>

                            <div class="modal-footer text-center justify-content-center">
                                <button type="button" class="btn bg-custom" data-dismiss="modal">Cancel</button>
                            </div>

                            </div>
                        </div>
                        </div>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Title</th>
                                    <th class="mobile-hidden">Date Posted</th>
                                    <th class="mobile-hidden">Author</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $x = 0; 
                                $news = News::get();
                                foreach ($news as $article) {
                                    echo '<tr><td class="align-middle">';
                                    echo $article['title'];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $article['dateposted'];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $article['author'];
                                    echo '</td><td class="align-middle">';
                                    echo '&nbsp;<button value="'.$article['id'].'" id="articleedit" data-toggle="modal" data-target="#article'.$x.'editmodal" class="btn btn-primary text-light" name="edit"><i class="fa fa-edit"></i></button>';
                                    echo '&nbsp;<button data-id="'.$article['id'].'" class="btn btn-danger text-light deleteArticle"><i class="fa fa-trash"></i></button>';
                                    echo '</td>';
                                    $x++;
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        $x = 0;
                        foreach ($news as $article) {
                            echo 
                            '
                            <div class="modal fade" id="article'.$x.'editmodal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit News Article</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
                                                <input hidden name="action" value="editarticle">
                                                <input hidden name="id" value="'.$article['id'].'">
                                                <div class="form-group">
                                                    <label>Title</label>
                                                    <input type="text" value="'.$article["title"].'" class="form-control" name="title">
                                                </div>
                                                <div class="form-group">
                                                    <label>Content</label>
                                                    <textarea class="form-control" name="content">'.$article["content"].'</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Author</label>
                                                    <input readonly type="text" value="'.$article["author"].'" class="form-control" name="author">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-ifc">Date Posted</label>
                                                    <input readonly type="text" value="'.$article["dateposted"].'" class="form-control" name="dateposted">
                                                </div>
                                                <input type="submit" class="btn bg-success" value="Save">
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn bg-danger" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ';
                            $x++;
                        }
                        ?>
                        <br />
                        <h4>New Article</h4>
                        <form action="/update.php" method="post">
                            <input hidden name="action" value="newarticle">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control" name="title">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="form-control" name="content"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Author</label>
                                <input readonly type="text" value="<?= escape($user->data()->name) ?>" class="form-control" name="author">
                            </div>
                            <input type="submit" class="btn bg-custom" value="Save">
                        </form>

                        <script>
                            $(".deleteArticle").click(function() {
                                var id = $(this).data('id');
                                $("#confirmNewsDelete-id").val(id);
                                $("#confirmNewsDelete").modal('show');
                            });
                        </script>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include '../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".<?= $ACTIVE_CATEGORY ?>").collapse('show');
        });
    </script>
</body>
</html>