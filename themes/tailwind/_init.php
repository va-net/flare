<?php
Router::add('/pireps/(\d+)', [new TailwindController, 'get_pirep']);
