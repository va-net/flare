<?php
Router::add('/pireps/(\d+)', [new TailwindController, 'get_pirep']);
Router::add('/profile', [new TailwindController, 'get_profile']);
Router::add('/profile', [new TailwindController, 'post_profile'], 'post');
