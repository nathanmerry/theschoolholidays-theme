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
$holidayTerms = [
  'feb',
  'easter',
  'may',
  'summer',
  'oct',
  'christmas',
];
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
              <h1 class="text-4xl text-[#273c75] font-bold font-oswald">SCHOOL HOLIDAY CAMPS</h1>
            </div>
            <h2 class="mx-2 text-xl transform" style="transform: translateY(50%);">
              <span class="inline-block text-[#273c75] px-5 py-2 text-sm font-bold bg-white rounded shadow lg:text-base font-oswald">FIND THE NEAREST SCHOOL HOLIDAY CAMP FOR YOUR KIDS</span>
            </h2>
          </div>
        </div>
        <div class="pt-5 pb-10">
          <form id="mainForm" class=" bg-[#fdb900] max-w-[900px] mx-auto px-[3px] py-[3px] flex md:flex-row flex-col items-end rounded-lg overflow-hidden gap-[3px]">
            <div class="w-full md:w-3/12">
              <?php if ($camps->errors['postcode'] ?? null) : ?>
                <p id="postcode-validation" class="mt-2 italic text-error"><?php echo $camps->errors['postcode'] ?></p>
              <?php endif; ?>
              <!-- <label class="text-white label" for="">Postcode</label> -->
              <input class="w-full mt-0 input input-bordered" type="text" name="postcode" required placeholder="Enter Postcode" value="<?php echo formatUKPostcode($_GET['postcode'] ?? null) ?>">
            </div>

            <div class="w-full md:w-3/12">
              <!-- <label class="text-white label" for="">Distance (kilometers)</label> -->
              <input class="w-full mt-0 input input-bordered" type="number" min="0" name="distance" placeholder="Distance (Miles)" required value="<?php echo $_GET['distance'] ?? null ?>">
            </div>

            <div class="w-full md:w-3/12">
              <button id="dropdownDefault" data-dropdown-toggle="dropdown" class="bg-white focus:ring-4 w-full text-black focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center btn" type="button">
                Holiday Term
                <svg class="w-4 h-4 ml-2" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              <!-- Dropdown menu -->
              <div id="dropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                  Category
                </h6>
                <ul class="space-y-2 text-sm" aria-labelledby="dropdownDefault">
                  <?php foreach ($holidayTerms as $term) : ?>
                    <li class="flex items-center">
                    <input
                      id="<?php echo $term . '-checkbox' ?>"
                      type="checkbox"
                      name="term[]"
                      <?php echo isChecked($term) ? 'checked' : '' ?>
                      value="<?php echo strtolower($term); ?>"
                      class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500"
                    />

                      <label for="apple" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        <?php echo strtoupper($term) ?>
                      </label>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>

            <div class="w-full md:w-3/12">
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
            Found <strong><?php echo count($camps->campData) ?> holiday camps</strong> within <?php echo $_GET['distance'] ?> miles of <i class="font-semibold"><?php echo formatUKPostcode($_GET['postcode']) ?></i>
          <?php else : ?>
            <span class="font-medium">Showing all holiday camps. Search above to find camps within your area...</span>
          <?php endif; ?>
        </div>
        <div class="flex">
          <!-- <div class="w-[30%] pr-4 relative flex flex-col w-full h-full max-w-xs pb-12 overflow-y-auto bg-white">
            <form class="text-sm border-t border-gray-200">
              <div class="py-6 border-t border-gray-200 ">
                <h3 class="flow-root -mx-2 -my-3">
                  <button type="button" class="flex items-center justify-between w-full px-2 py-3 text-gray-400 bg-white hover:text-gray-500" aria-controls="filter-section-mobile-0" aria-expanded="false">
                    <span class="font-medium text-gray-900">Holiday Term Filter</span>
                    <span class="flex items-center ml-6">
                    </span>
                  </button>
                </h3>
                <div class="pt-6" id="filter-section-mobile-0">
                  <div class="space-y-6">
                    <div class="flex items-center">
                      <input id="filter-term-0" name="term[]" value="all" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-term-0" class="flex-1 min-w-0 ml-3 text-gray-500">All</label>
                    </div>
                    <div class="flex items-center">
                      <input id="filter-term-0" name="term[]" value="feb" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-term-0" class="flex-1 min-w-0 ml-3 text-gray-500">Feb</label>
                    </div>
                    <div class="flex items-center">
                      <input id="filter-term-1" name="term[]" value="easter" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-term-1" class="flex-1 min-w-0 ml-3 text-gray-500">Easter</label>
                    </div>
                    <div class="flex items-center">
                      <input id="filter-term-2" name="term[]" value="may" type="checkbox" checked class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-term-2" class="flex-1 min-w-0 ml-3 text-gray-500">May</label>
                    </div>
                    <div class="flex items-center">
                      <input id="filter-term-3" name="term[]" value="summer" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-term-3" class="flex-1 min-w-0 ml-3 text-gray-500">Summer</label>
                    </div>
                    <div class="flex items-center">
                      <input id="filter-term-4" name="term[]" value="oct" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-term-4" class="flex-1 min-w-0 ml-3 text-gray-500">Oct</label>
                    </div>
                    <div class="flex items-center">
                      <input id="filter-mobile-color-5" name="color[]" value="purple" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <label for="filter-mobile-color-5" class="flex-1 min-w-0 ml-3 text-gray-500">Christmas</label>
                    </div>
                    <button class="w-full btn !text-[#273c75] outline-none border-0 text-white border border-[#273c75] bg-[transparent]">Refresh</button>
                  </div>
                </div>
              </div>
            </form>
          </div> -->
          <div class="flex grid flex-1 gap-8 md:grid-cols-2 lg:grid-cols-2">
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

  <script>
    window.addEventListener("load", function(event) {
      document.querySelector('[data-dropdown-toggle="dropdown"]').click();
    });
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

  <?php wp_footer() ?>
</body>

</html>