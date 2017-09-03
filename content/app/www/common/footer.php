<footer class="black pos-rlt">
  <div class="footer dk">
    <div class="text-center container p-y-lg">
      <div class="clearfix text-lg m-t">
        <?php $this->translator->e("Sample Application");?>
      </div>
      <div class="nav m-y text-primary-hover">
        <a class="nav-link m-x" href="<?php echo $this->app->getSiteUrl();?>">
          <span class="nav-text"><?php $this->translator->e("Home");?></span>
        </a>
      </div>
      <div class="block clearfix">
        <a href="" class="btn btn-icon btn-social rounded btn-sm m-r"> <i class="fa fa-twitter"></i> <i
          class="fa fa-twitter light-blue"></i>
        </a> <a href="" class="btn btn-icon btn-social rounded btn-sm"> <i class="fa fa-facebook"></i> <i
          class="fa fa-facebook indigo"></i>
        </a>
      </div>
    </div>
    <div class="b b-b"></div>
    <div class="p-a-md">
      <div class="row footer-bottom">
        <div class="col-sm-8">
          <small class="text-muted"><?php $this->translator->tPf("&copy; Copyright %s - All rights reserved", date("Y"));?></small>
        </div>
        <div class="col-sm-4">
          <div class="text-sm-right text-xs-left">
            <strong></strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>
