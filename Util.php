<?php
    class Util {
        public function redirect($url) {
            header("Location:" . $url);
            exit;
        }
    }