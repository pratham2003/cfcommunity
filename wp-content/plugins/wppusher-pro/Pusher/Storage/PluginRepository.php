<?php

namespace Pusher\Storage;

use Pusher\Git\Repository;
use Pusher\Plugin;
use Pusher\Pusher;
use Pusher\Storage\PackageModel;

class PluginRepository
{
    protected $pusher;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    public function allPusherPlugins()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE type = 1");

        $plugins = array();

        foreach ($rows as $row) {

            if ( ! file_exists(WP_PLUGIN_DIR . "/" . $row->package)) continue;

            $array = get_plugin_data(WP_PLUGIN_DIR . "/" . $row->package);
            $plugins[$row->package] = Plugin::fromWpArray($row->package, $array);
            $repository = new Repository($row->repository);
            $repository->setBranch($row->branch);
            $plugins[$row->package]->setRepository($repository);
            $plugins[$row->package]->setPushToDeploy($row->ptd);
        }

        return $plugins;
    }

    public function editPlugin($file, $input)
    {
        global $wpdb;

        $model = new PackageModel(array(
            'package' => $file,
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
        $plugins = get_plugins();

        foreach ($plugins as $file => $pluginInfo) {
            $tmp = explode('/', $file);
            $currentSlug = $tmp[0];

            if ($currentSlug === $slug) break;

            $file = null;
        }

        return Plugin::fromWpArray($file, $pluginInfo);
    }

    public function pusherPluginFromRepository($repository)
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $model = new PackageModel(array('repository' => $repository));

        $row = $wpdb->get_row("SELECT * FROM $table_name WHERE type = 1 AND repository = '{$model->repository}'");

        if (is_null($row)) return;

        if ( ! file_exists(WP_PLUGIN_DIR . "/" . $row->package)) return;

        $array = get_plugin_data(WP_PLUGIN_DIR . "/" . $row->package);
        $plugin = Plugin::fromWpArray($row->package, $array);

        $repository = $this->pusher->repositoryFactory->build(
            $row->host,
            $row->repository
        );

        $repository->setBranch($row->branch);
        $plugin->setRepository($repository);
        $plugin->setPushToDeploy($row->ptd);

        if ($row->private)
            $plugin->repository->makePrivate();

        return $plugin;
    }

    public function store(Plugin $plugin)
    {
        global $wpdb;

        $model = new PackageModel(array(
            'package' => $plugin->file,
            'repository' => $plugin->repository,
            'branch' => $plugin->repository->getBranch(),
            'status' => 1,
            'host' => $plugin->repository->code,
            'private' => $plugin->repository->isPrivate()
        ));

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE package = '$model->package'");

        if ($count !== '0') {

            return $wpdb->update(
                $table_name,
                array(
                    'branch' => $model->branch,
                    'status' => $model->status
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
                'type' => 1,
                'status' => $model->status,
                'host' => $model->host,
                'private' => $model->private
            )
        );
    }
}
