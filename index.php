<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <?php wp_head(); ?>
  <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>" type="text/css" media="all" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Comforter&family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Oswald:wght@700&display=swap" rel="stylesheet">
  <title>The School Holidays</title>
  <style>
    body {
      font-family: 'Comforter', cursive;
      font-family: 'Open Sans', sans-serif;
    }

    @media (min-width: 1024px) {
      #mainForm {
        /* transform: translateY(-55px);
        margin-bottom: -55px */
      }
    }
  </style>
</head>

<?php
$hasInput = !!($_GET['postcode'] ?? null) && !!($_GET['distance'] ?? null);
$camps = new Camps();
$camps->setCampData($_GET['postcode'] ?? null, $_GET['distance'] ?? null);
?>

<body class="w-full h-screen text-black bg-base">
  <div>
    <div class="bg-[#273c75]">
      <div class="container flex items-center justify-between py-2 text-center lg:py-3">
        <img src="https://media.igms.io/2023/09/29/1698583256990-9e1843af-6c71-4f7a-8826-98bfa90b6de7.png" alt="Image Description" class=h-[50px]>
      </div>
    </div>
    <div class="bg-center bg-cover bg-base-200">
      <div class="container overflow-hidden">
        <div class="flex justify-center text-[#273c75] py-10">
          <div class="inline-block text-center text-white">
            <div class="px-16 pb-2 lg:px-24">
              <h1 class="text-4xl text-[#273c75] font-bold font-oswald">SUMMER HOLIDAY CAMP</h1>
            </div>
            <h2 class="mx-2 text-xl transform" style="transform: translateY(50%);">
              <span class="inline-block text-[#273c75] px-5 py-2 text-sm font-bold bg-white rounded shadow lg:text-base font-oswald">FIND THE NEAREST HOLIDAY CAMP FOR YOUR KIDS</span>
            </h2>
          </div>
        </div>
        <div class="pt-5 pb-10">
          <form id="mainForm" class=" bg-[#fdb900] max-w-[800px] mx-auto px-[3px] py-[3px] flex items-end rounded-lg overflow-hidden gap-[3px]">
            <div class="w-5/12">
              <?php if ($camps->errors['postcode'] ?? null) : ?>
                <p id="postcode-validation" class="mt-2 italic text-error"><?php echo $camps->errors['postcode'] ?></p>
              <?php endif; ?>
              <!-- <label class="text-white label" for="">Postcode</label> -->
              <input class="w-full mt-0 input input-bordered" type="text" name="postcode" required placeholder="Enter Postcode" value="<?php echo $_GET['postcode'] ?? null ?>">
            </div>

            <div class="w-5/12">
              <!-- <label class="text-white label" for="">Distance (kilometers)</label> -->
              <input class="w-full mt-0 input input-bordered" type="number" min="0" name="distance" placeholder="Distance (Miles)" required value="<?php echo $_GET['distance'] ?? null ?>">
            </div>

            <div class="w-2/12">
              <button class="w-full btn outline-none border-0 text-white bg-[#273c75] hover:!bg-[#ffd04e]">Search</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <section class="lg:container">
    </section>
    <section class="container text-left">
      <?php if (count($camps->campData)) : ?>
        <div class="py-7">
          <?php if ($hasInput) : ?>
            Found <strong><?php echo count($camps->campData) ?> holiday camps</strong> within <?php echo $_GET['distance'] ?> miles of <?php echo $_GET['postcode'] ?>
          <?php else : ?>
            <span class="font-medium">Showing all holiday camps. Search above to find camps within your area...</span>
          <?php endif; ?>
        </div>
        <div class="flex grid gap-8 md:grid-cols-3 lg:grid-cols-3">
          <?php foreach ($camps->campData as $camp) : ?>
            <div class="flex flex-col justify-between w-full overflow-hidden rounded bg-base-100">
              <div class="flex-1">
                <div class="relative">
                  <div class="h-[140px] md:h-[220px] flex items-center bg-gray-100 overflow-hidden">
                    <img src="<?php echo $camp['logo'] ?>" class="w-full" alt="Image Description">
                  </div>
                </div>
                <div class="w-full px-2 py-4 text-black">
                  <h2 class="block my-0 mb-1 mb-4 text-2xl font-semibold text-black"><?php echo $camp['name'] ?></h2>
                  <a class="block mb-4 font-medium text-black text-opacity-60">
                    <?php echo $camp['location'] ?>
                  </a>
                  <div class="flex flex-col justify-between mb-4">
                    <?php if ($camp['min_age'] ?? null && $camp['max_age']) : ?>
                      <div class="">
                        <span class="inline-block mr-1 font-semibold">
                          Ages:
                        </span>
                        <span class=""><?php echo $camp['min_age'] ?> - <?php echo $camp['max_age'] ?></span>
                      </div>
                    <?php endif; ?>
                    <?php if ($camp['opening_time'] ?? null && $camp['closing_time'] ?? null) : ?>
                      <div class="">
                        <span class="inline-block mr-1 font-semibold ">
                          Hours:
                        </span>
                        <span class=""><?php echo $camp['opening_time'] ?> - <?php echo $camp['closing_time'] ?></span>
                      </div>
                    <?php endif; ?>
                    <?php if ($camp['extended_closing_time'] ?? null) : ?>
                      <div class="">
                        <span class="font-semibold">
                          Ext. Hours:
                        </span>
                        <span class=""><?php echo $camp['extended_opening_time'] ?> - <?php echo $camp['extended_closing_time'] ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                  <?php if ($camp['opening_months'] ?? null) : ?>
                    <div class="mb-4">
                      <span class="inline-block mr-1 font-semibold">
                        <i class="text-primary fa-regular fa-calendar"></i>
                      </span>
                      <span class=""><?php echo implode(', ', $camp['opening_months']) ?></span>
                    </div>
                  <?php endif; ?>
                  <?php if ($camp['childcare_vouchers_tax_free']) : ?>
                    <div class="mb-4">
                      <div>
                        <i class="mr-1 text-primary fa-regular fa-star"></i>
                        Accepts Childcare Vouchers
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <?php if ($camp['url'] ?? null) : ?>
                <div class="w-full px-2 pb-4">
                  <a href="<?php echo $camp['url'] ?>" class="w-full no-underline btn bg-[#273c75] text-white hover:bg-[#273c75]">Visit Website <i class="fa-solid fa-arrow-right"></i></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div class="py-6">
          There are no results within that distance from the given postcode
        </div>
      <?php endif; ?>
    </section>
  </div>
  <div class="bg-[#273c75] mt-10 text-sm text-white">
    <div class="container flex items-center justify-between py-5">
      <div class="">
        Need more information? Email us at <a href="mailto:info@theschoolholidays.co.uk" class="">info@theschoolholidays.co.uk</a>
      </div>
      <div class="">
        <img src="https://media.igms.io/2023/09/29/1698583256990-9e1843af-6c71-4f7a-8826-98bfa90b6de7.png" alt="Image Description" class=h-[50px]>
      </div>
    </div>
  </div>
  <?php wp_footer() ?>
</body>

</html>