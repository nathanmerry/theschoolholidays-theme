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
        transform: translateY(-55px);
        margin-bottom: -55px
      }
    }
  </style>
</head>

<?php
$hasInput = !!($_GET['postcode'] ?? null) && !!($_GET['distance'] ?? null);
$camps = new Camps();
$camps->setCampData($_GET['postcode'] ?? null, $_GET['distance'] ?? null);
?>

<body class="w-full h-screen bg-base text-base-content">
  <div class="">
    <div class="container flex items-center justify-between py-2 text-center lg:py-3">
      <img src="https://media.igms.io/2023/09/29/1698583256990-9e1843af-6c71-4f7a-8826-98bfa90b6de7.png" alt="Image Description" class=h-[50px]>
    </div>
    <div class="overflow-hidden bg-center bg-cover bg-base-200" style="transform: scaleX(-1); background-image: url(https://media.igms.io/2023/09/29/1698583258087-f5ca51eb-71b9-4370-b1d5-6f7198f1f2e6.png)">
      <divs style="transform: scaleX(-1)" class="container flex justify-center pt-10 pb-12 lg:pt-20 lg:pb-28">
        <div class="inline-block text-center text-white border-4 border-white">
          <div class="px-16 pt-10 pb-2 lg:px-24">
            <h1 class="text-4xl font-bold font-oswald">SUMMER HOLIDAY CAMP</h1>
          </div>
          <h2 class="text-xl text-[#2b6fb1] mx-2 transform" style="transform: translateY(50%);">
            <span class="inline-block px-5 py-2 text-sm font-bold bg-white rounded shadow lg:text-base font-oswald">FIND THE NEAREST HOLIDAY CAMP FOR YOUR KIDS</span>
          </h2>
        </div>
      </divs>
    </div>
    <section class="lg:container">
      <form id="mainForm" class="px-4 grid lg:grid-cols-3 items-end gap-2 lg:gap-8 pt-2 pb-4 bg-[#2b6fb1]">
        <div class="">
          <?php if ($camps->errors['postcode'] ?? null) : ?>
            <p id="postcode-validation" class="mt-2 italic text-error"><?php echo $camps->errors['postcode'] ?></p>
          <?php endif; ?>
          <label class="text-white label" for="">Postcode</label>
          <input class="w-full input input-bordered" type="text" name="postcode" required placeholder="Enter Postcode" value="<?php echo $_GET['postcode'] ?? null ?>">
        </div>

        <div class="">
          <label class="text-white label" for="">Distance (kilometers)</label>
          <input class="w-full input input-bordered" type="number" min="0" name="distance" placeholder="Distance (KM)" required value="<?php echo $_GET['distance'] ?? null ?>">
        </div>

        <div class="w-full">
          <label class="label"></label>
          <button class="w-full btn outline-none border-0 text-[#203240] bg-[#fdb900] hover:!bg-[#ffd04e]">Search</button>
        </div>
      </form>
    </section>
    <section class="container text-left">
      <?php if (count($camps->campData)) : ?>
        <div class="py-7">
          <?php if ($hasInput) : ?>
            Found <strong><?php echo count($camps->campData) ?> holiday camps</strong> within <?php echo $_GET['distance'] ?> kilometers of <?php echo $_GET['postcode'] ?>
          <?php else : ?>
            <span class="font-medium">Showing all holiday camps. Search above to find camps within your area...</span>
          <?php endif; ?>
        </div>
        <div class="flex grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($camps->campData as $camp) : ?>
            <div class="flex flex-col justify-between w-full overflow-hidden rounded shadow-xl card bg-base-100">
              <div class="flex-1">
                <div class="relative">
                  <div class="h-[140px] md:h-[220px] flex items-center bg-gray-100 overflow-hidden">
                    <img src="<?php echo $camp['logo'] ?>" class="w-full" alt="Image Description">
                  </div>
                  <?php if ($camp['min_age'] ?? null && $camp['max_age']) : ?>
                    <div class="absolute top-0 right-0 p-2 text-xl text-white font-caveatBrush bg-[#2b6fb1]">
                      <div class="text-sm">ages</div>
                      <div><span class="font-bold"><?php echo $camp['min_age'] ?> - <?php echo $camp['max_age'] ?></span> <span class="text-xs">years</span></div>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="w-full px-5 py-4 text-base-content">
                  <h2 class="block my-0 mb-1 text-3xl font-semibold font-caveatBrush text-base-content"><?php echo $camp['name'] ?></h2>
                  <a class="block mb-4 font-medium text-opacity-60 text-base-content">
                    <?php echo $camp['location'] ?>
                  </a>
                  <div class="flex flex-col justify-between mb-4">
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
                <div class="w-full px-5 pb-4">
                  <a href="<?php echo $camp['url'] ?>" class="w-full no-underline btn bg-[#2b6fb1] text-white hover:bg-[#63809d]">Visit Website <i class="fa-solid fa-arrow-right"></i></a>
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
  <script>
    const validateUKPostcode = (postcode) => {
      postcode = postcode.replace(/\s/g, '');
      const pattern = /^(GIR 0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]|[A-HK-Y][0-9]([0-9]|[ABEHMNPRV-Y])))[0-9][ABD-HJLNP-UW-Z]{2})$/i;
      return pattern.test(postcode);
    }

    const validateForm = (event) => {
      const {
        distance: {
          value: {
            distance
          }
        },
        postcode: {
          value: postcode
        }
      } = event.target.elements

      // postcode-validation
      // const postcodeValue = postcodeInput.value;

      if (!validateUKPostcode(postcode)) {
        event.preventDefault();
        const postcodeValidation = document.getElementById('postcode-validation');
        postcodeValidation.classList.remove('hidden')
      } else {
        postcodeValidation.classList.add('hidden')
      }
    }

    const form = document.getElementById('mainForm');

    // const postcode = "EC1A 1BB";
    // if (validateUKPostcode(postcode)) {
    //   console.log("Valid UK postcode");
    // } else {
    //   console.log("Invalid UK postcode");
    // }
  </script>
</body>

</html>