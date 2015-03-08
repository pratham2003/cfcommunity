<?php

namespace Pusher\Storage;

use Pusher\Git\Repository;
use Pusher\Pusher;
use Pusher\Theme;
use Pusher\Storage\PackageModel;
use WP_Theme;

class ThemeRepository
{
    protected $pusher;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    public function allPusherThemes()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE type = 2");

        $themes = array();

        foreach ($rows as $row) {

            // This is our time to do some cleaning up
            if ( ! file_exists(get_theme_root() . "/" . $row->package)) {
                $this->delete($row->id);
                continue;
            }

            $object = wp_get_theme($row->package);
            $themes[$row->package] = Theme::fromWpThemeObject($object);
            $repository = new Repository($row->repository);
            $repository->setBranch($row->branch);
            $themes[$row->package]->setRepository($repository);
            $themes[$row->package]->setPushToDeploy($row->ptd);
        }

        return $themes;
    }

    public function delete($id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $wpdb->delete($table_name, array('id' => sanitize_text_field($id)));
    }

    public function editTheme($stylesheet, $input)
    {
        global $wpdb;

        $model = new PackageModel(array(
            'package' => $stylesheet,
            'repository' => $input['repository'],
            'branch' => $input['branch'],
            'ptd' => $input['ptd']
        ));

        $table_name = $wpdb->prefix . 'wppusher_packages';

        return $wpdb->update(
            $table_name,
            array(
                'repository' => $model->repository,
                'branch' => $model->branch,
                'ptd' => $model->ptd
            ),
            array('package' => $model->package)
        );
    }

    public function fromSlug($slug)
    {
        $wpTheme = wp_get_theme($slug);

        return Theme::fromWpThemeObject($wpTheme);
    }

    public function pusherThemeFromRepository($repository)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $model = new PackageModel(array('repository' => $repository));

        $row = $wpdb->get_row("SELECT * FROM $table_name WHERE type = 2 AND repository = '{$model->repository}'");

        if (is_null($row)) return;

        if ( ! file_exists(get_theme_root() . "/" . $row->package)) return;

        $object = wp_get_theme($row->package);
        $theme = Theme::fromWpThemeObject($object);

        $repository = $this->pusher->repositoryFactory->build(
            $row->host,
            $row->repository
        );

        $repository->setBranch($row->branch);
        $theme->setRepository($repository);
        $theme->setPushToDeploy($row->ptd);

        if ($row->private)
            $theme->repository->makePrivate();

        return $theme;
    }

    public function store(Theme $theme)
    {
        global $wpdb;

        $model = new PackageModel(array(
            'package' => $theme->stylesheet,
            'repository' => $theme->repository,
            'branch' => $theme->repository->getBranch(),
            'status' => 1,
            'host' => $theme->repository->code,
            'private' => $theme->repository->isPrivate()
        ));

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE package = '$model->package'");

        if ($count !== '0') {

            return $wpdb->update(
                $table_name,
                array(
                    'status' => $model->status,
                    'branch' => $model->branch
                ),
                array('package' => $model->package)
            );

        }

        return $wpdb->insert(
            $table_name,
            array(
                'package' => $model->package,
                'repository' => $model->repository,
                'branch' => $model->branch,
                'type' => 2,
                'status' => $model->status,
                'host' => $model->host,
                'private' => $model->private
            )
        );
    }
}
