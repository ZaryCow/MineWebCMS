<?php
$this->EyPlugin = new EyPluginComponent;
?>
<section class="content">
  <div class="row">
    <div class="col-md-12">

      <div class="ajax"></div>

      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?= $Lang->get('PLUGIN__LIST') ?></h3>
        </div>

        <div class="box-body">

          <?php
          $pluginList = $this->EyPlugin->loadPlugins();
            if(!empty($pluginList)) {
          ?>
            <table class="table table-bordered" id="plugin-installed">
              <thead>
                <tr>
                  <th><?= $Lang->get('GLOBAL__NAME') ?></th>
                  <th><?= $Lang->get('GLOBAL__AUTHOR') ?></th>
                  <th><?= $Lang->get('GLOBAL__CREATED') ?></th>
                  <th><?= $Lang->get('GLOBAL__VERSION') ?></th>
                  <th><?= $Lang->get('PLUGIN__LOADED') ?></th>
                  <th><?= $Lang->get('GLOBAL__STATUS') ?></th>
                  <th><?= $Lang->get('GLOBAL__ACTIONS') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                  foreach ($pluginList as $key => $value) { ?>
                  <tr>
                    <td><?= $value->name ?></td>
                    <td><?= $value->author ?></td>
                    <td><?= $Lang->date($value->DBinstall) ?></td>
                    <td><?= $value->version ?></td>
                    <td>
                      <?= ($value->isValid) ? '<span class="label label-success">'.$Lang->get('GLOBAL__YES').'</span>' : '<span class="label label-danger">'.$Lang->get('GLOBAL__NO').'</span>' ?>
                    </td>
                    <td>
                      <?= ($value->active) ? '<span class="label label-success">'.$Lang->get('GLOBAL__ENABLED').'</span>' : '<span class="label label-danger">'.$Lang->get('GLOBAL__DISABLED').'</span>' ?>
                    </td>
                    <td>
                      <?php if($value->active) { ?>
                        <a href="<?= $this->Html->url(array('controller' => 'plugin', 'action' => 'disable/'.$value->DBid, 'admin' => true)) ?>" class="btn btn-info disable"><?= $Lang->get('GLOBAL__DISABLED') ?></a>
                       <?php } else { ?>
                        <a href="<?= $this->Html->url(array('controller' => 'plugin', 'action' => 'enable/'.$value->DBid, 'admin' => true)) ?>" class="btn btn-info enable"><?= $Lang->get('GLOBAL__ENABLED') ?></a>
                       <?php } ?>
                      <a onClick="confirmDel('<?= $this->Html->url(array('controller' => 'plugin', 'action' => 'delete/'.$value->DBid, 'admin' => true)) ?>')" class="btn btn-danger delete"><?= $Lang->get('GLOBAL__DELETE') ?></a>
                      <?php if($value->version != $this->EyPlugin->getPluginLastVersion($value->apiID)) { ?>
                        <a href="<?= $this->Html->url(array('controller' => 'plugin', 'action' => 'update/'.$value->apiID.'/'.$value->slug, 'admin' => true)) ?>" class="btn btn-warning update"><?= $Lang->get('GLOBAL__UPDATE') ?></a>
                      <?php } ?>
                    </td>
                  </tr>
                  <?php } ?>
              </tbody>
            </table>
          <?php } else {
            echo '<div class="alert alert-danger">'.$Lang->get('PLUGIN__NONE_INSTALLED').'</div>';
          } ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?= $Lang->get('PLUGIN__AVAILABLE_FREE') ?></h3>
        </div>
        <div class="box-body">
          <?php
          $free_plugins = $this->EyPlugin->getFreePlugins();
          if(!empty($free_plugins)) { ?>
            <table class="table table-bordered" id="plugin-not-installed">
              <thead>
                <tr>
                  <th><?= $Lang->get('GLOBAL__NAME') ?></th>
                  <th><?= $Lang->get('GLOBAL__AUTHOR') ?></th>
                  <th><?= $Lang->get('GLOBAL__VERSION') ?></th>
                  <th><?= $Lang->get('GLOBAL__ACTIONS') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($free_plugins as $key => $value) { ?>
                <tr plugin-apiID="<?= $value['apiID'] ?>">
                  <td><?= $value['name'] ?></td>
                  <td><?= $value['author'] ?></td>
                  <td><?= $value['version'] ?></td>
                  <td>
                    <btn class="btn btn-success install" apiID="<?= $value['apiID'] ?>" slug="<?= $value['slug'] ?>"><?= $Lang->get('PLUGIN__INSTALL') ?></btn>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="alert alert-danger"><b><?= $Lang->get('GLOBAL__ERROR') ?> : </b><?= $Lang->get('PLUGIN__NONE_AVAILABLE') ?></div>
          <?php } ?>

        </div>
      </div>
    </div>
  </div>
</section>
<script type="text/javascript">
  $('.install').click(function(e) {
    e.preventDefault();

    var apiID = $(this).attr('apiID');
    var slug = $(this).attr('slug');

    var btn = $(this);

    if(apiID !== undefined) {

      // Désactivation de toute action
      $('.install').each(function(e) {
        $(this).addClass('disabled');
      });
      $('.update').each(function(e) {
        $(this).addClass('disabled');
      });
      $('.delete').each(function(e) {
        $(this).addClass('disabled');
      });
      $('.enable').each(function(e) {
        $(this).addClass('disabled');
      });
      $('.disable').each(function(e) {
        $(this).addClass('disabled');
      });

      // Mise à jour du texte sur le bouton
      $(this).html('<?= $Lang->get('PLUGIN__INSTALL_LOADING') ?>...');

      // On préviens l'utilisateur avec un message plus clair
      $('.ajax').empty().html('<div class="alert alert-info"><?= $Lang->get('PLUGIN__INSTALL_LOADING') ?>...</b></div>').fadeIn(500);

      // On lance la requête
      $.get('<?= $this->Html->url(array('action' => 'install')) ?>/'+apiID+'/'+slug, function(data) {
        data = JSON.parse(data);
        if(data !== false) {

          if(data.statut == "success") {
            // on met le message
            $('.ajax').empty().html('<div class="alert alert-success"><b><?= $Lang->get('GLOBAL__SUCCESS') ?> : <?= $Lang->get('PLUGIN__INSTALL_SUCCESS') ?></b></div>').fadeIn(500);

            // on bouge le plugin dans le tableau dans les plugins installés
            $('table#plugin-not-installed').find('tr[plugin-apiID="'+apiID+'"]').slideUp(250);
            $('table#plugin-installed tr:last').after('<tr><td>'+data.plugin.name+'</td><td>'+data.plugin.author+'</td><td>'+data.plugin.dateformatted+'</td><td>'+data.plugin.version+'</td><td><span class="label label-success"><?= $Lang->get('GLOBAL__ENABLED') ?></span></td><td><a href="<?= $this->Html->url(array('controller' => 'plugin', 'action' => 'disable', 'admin' => true)) ?>'+data.plugin.DBid+'" class="btn btn-info"><?= $Lang->get('GLOBAL__DISABLED') ?></a><a onClick="confirmDel(\'<?= $this->Html->url(array('controller' => 'plugin', 'action' => 'delete', 'admin' => true)) ?>'+data.plugin.DBid+'\')" class="btn btn-danger"><?= $Lang->get('GLOBAL__DELETE') ?></a></td></tr>');

          } else if(data.statut == "error") {
            $('.ajax').empty().html('<div class="alert alert-error"><b><?= $Lang->get('GLOBAL__ERROR') ?> : '+data.msg+'</b></div>').fadeIn(500);
          } else {
            $('.ajax').empty().html('<div class="alert alert-error"><b><?= $Lang->get('GLOBAL__ERROR') ?> : <?= addslashes($Lang->get('INTERNAL_ERROR')) ?></b></div>').fadeIn(500);
          }

        }

        // On annule les désactivations
        $('.install').each(function(e) {
          $(this).removeClass('disabled');
        });
        $('.update').each(function(e) {
          $(this).removeClass('disabled');
        });
        $('.delete').each(function(e) {
          $(this).removeClass('disabled');
        });
        $('.enable').each(function(e) {
          $(this).removeClass('disabled');
        });
        $('.disable').each(function(e) {
          $(this).removeClass('disabled');
        });

        // On remet le texte par défaut
        btn.html('<?= $Lang->get('PLUGIN__INSTALL') ?>');

        return;
      });


    }

    return;

  });
</script>
