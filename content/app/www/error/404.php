<div class="p-y-lg">
  <div class="amber bg-auto w-full">
    <div class="text-center pos-rlt p-y-md">
      <h1 class="text-shadow m-a-0 text-white text-4x">
        <span class="text-2x font-bold block m-t-lg">404</span>
      </h1>
      <h2 class="h1 m-y-lg text-black"><?php $this->translator->e("OOPS!");?></h2>
      <p class="h5 m-y-lg text-u-c font-bold text-black">
        <?php $this->translator->e("Sorry! the page you are looking for doesn't exist.");?>
      </p>
      <a class="md-btn amber-700 md-raised p-x-md" href="<?php echo $this->app->getSiteUrl();?>">
        <span class="text-white"><?php $this->translator->e("Go to homepage");?></span>
      </a>
    </div>
  </div>
</div>