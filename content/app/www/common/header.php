<header>
  <nav class="navbar navbar-md navbar-fixed-top white">
    <div class="container">
      <a data-toggle="collapse" data-target="#navbar-1" class="navbar-item pull-right hidden-md-up m-a-0 m-l">
        <i class="fa fa-bars"></i>
      </a>
      <a class="navbar-brand md" href="<?php echo $this->app->getSiteUrl();?>">
        <img src="<?php echo $this->getImgPath("logo.png");?>" />
        <span class="hidden-folded inline text m-l-0"><?php $this->translator->e("Sample Application");?></span>
      </a>

      <ul class="nav navbar-nav pull-right">
        <li class="nav-item">
		<?php if ($this->user->isSignedIn) {?>
		  <a class="btn-signout nav-link" href="<?php echo $this->app->getSiteUrl();?>signin/?logout=true">
            <span class="btn btn-sm rounded blue-800 _700"><?php $this->translator->e("Sign out");?></span>
          </a>
		<?php } else {?>
		  <a class="btn-signin nav-link" href="<?php echo $this->app->getSiteUrl();?>signin/">
            <span class="btn btn-sm rounded blue-800 _700"><?php $this->translator->e("Sign in");?></span>
          </a>
		<?php }?>
        </li>
      </ul>

      <div class="collapse navbar-toggleable-sm text-center white" id="navbar-1">
        <ul class="nav navbar-nav nav-active-border top b-primary pull-right m-r-md">
          <li class="nav-item">
            <a class="nav-link b-info" href="<?php echo $this->app->getSiteUrl();?>">
              <span class="nav-text"><?php $this->translator->e("Home");?></span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>