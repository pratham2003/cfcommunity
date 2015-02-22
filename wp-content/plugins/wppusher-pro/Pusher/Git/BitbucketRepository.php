<?php

namespace Pusher\Git;

use Exception;

class BitbucketRepository extends Repository
{
    public $code = 'bb';

    public function getZipUrl()
    {
        $url = 'https://bitbucket.org/' . $this->handle . '/get/' . $this->getBranch() . '.zip';

        if ($this->isPrivate()) {
            add_filter( 'http_request_args', array($this, 'bitbucketBasicAuth'), 10, 2 );
        }

        return $url;
    }

    public function bitbucketBasicAuth($args, $url)
    {
        if ( ! strstr($url, 'https://bitbucket.org/'))
            return $args;

        $user = get_option('bb_user');
        $pass = get_option('bb_pass');

        if ( is_string($user) && $user === '')
            throw new Exception('No Bitbucket username stored.');

        if ( is_string($pass) && $pass === '')
            throw new Exception('No Bitbucket password stored.');

        $args['headers']['Authorization'] = 'Basic ' . base64_encode("{$user}:{$pass}");

        return $args;
    }
}
