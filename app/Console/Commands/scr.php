<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use App\Store;
use App\Reservation;
use DateTime;
use LengthException;

class scr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('max_execution_time', 0);
        $puppeteer = new Puppeteer();
        $browser = $puppeteer->launch([
            'args' => ['--no-sandbox', '--disable-setuid-sandbox'],
            // 'headless' => false,
            'timeout' => 0,
            'read_timeout' => 0,
        ]);
        $https = 'https://omakase.in/r/';
        $reservations = '/reservations/new';

        $page = $browser->newPage();
        $page->goto('https://omakase.in/users/sign_in');

        $page->waitForSelector('#new_user');

        $page->type('#user_email', 'otani@seabose.com');
        $page->type('#user_password', 'test1234');
        $page->click('input[name="commit"]');
        $page->waitFor(5000);

        $page->goto('https://omakase.in/r');





        $dimensions = $page->evaluate(JsFunction::createWithBody("
            var divs = document.getElementsByClassName('c-restaurant_item');
            var array = [];
            divs.forEach(div => {
                var link = div.getElementsByTagName('a');
                var href = link[0].href
                var tenpo = href.split('/');
                var tenpoNo = tenpo[5];
                array.push(tenpoNo);
            });
            return array;
         "));


        foreach ($dimensions as $dimension) {

            // store_no???DB????????????????????????
            $store = Store::query()->where('store_no', '=', $dimension)->first();
            if ($store == null) {
                $store_url = $https . $dimension;
                $page->goto($store_url);
                $page->waitFor(5000);
                // ????????????????????????
                $store_data = $page->evaluate(JsFunction::createWithBody("

                    var title = document.getElementsByClassName('ui header p-r_title');
                    var store_name = title[0].innerText;

                    var table = document.getElementsByClassName('ui very basic table');
                    var td_list = table[0].getElementsByTagName('td');
                    var ad_array = [];
                    for(i=0; i< td_list.length; i++){
                        if(td_list[i].innerText == '??????'){
                            var address = td_list[i].nextElementSibling;
                            var address_text = address.innerText;
                        }
                    }

                    ad_array.push(store_name, address_text);
                    return ad_array;
                "));

                // ?????????????????????????????????
                $store = Store::create([
                    'name' => $store_data[0],
                    'address' => $store_data[1],
                    'store_no' => $dimension
                ]);
            }

            $yoyaku = $https . $dimension . $reservations;

            $page->goto($yoyaku);
            $page->waitFor(5000);
            //p-reservation?????????????????????????????????????????????????????????????????????????????????
            $table = $page->querySelector('.p-reservation_customer_table');
            // ??????????????????????????????????????????????????????
            $store->reservations()->delete();

            if ($table == null) {
                continue;
            }

            $dates = $page->evaluate(JsFunction::createWithBody("

               var div = document.getElementsByClassName('p-reservation_customer_table');
               var a_lists = div[0].getElementsByTagName('a');
               var array = [];
               for(i=0; i< a_lists.length; i++){
                if(a_lists[i].className != 'disabled'){
                 var a_click = a_lists[i].innerText;

                 array.push(a_click);
                }
               }

                return array;
            "));


            // ??????????????????
            $month = $page->evaluate(JsFunction::createWithBody("
                var divs = document.getElementsByTagName('div');
                var month = divs[43].innerText

                return month;
            "));

            // ???????????????????????????????????????
            $right = $page->querySelector('.fa-chevron-right');
            // ??????????????????????????????????????????

            do {
                if ($right != null) {
                    $page->click('i.fa-chevron-right');
                    $page->waitFor(5000);

                    $next_dates = $page->evaluate(JsFunction::createWithBody("
                        var div = document.getElementsByClassName('p-reservation_customer_table');
                        var a_lists = div[0].getElementsByTagName('a');
                        var array = [];
                        for(i=0; i< a_lists.length; i++){
                            if(a_lists[i].className != 'disabled'){
                                array.push(a_lists[i].innerText);
                            }
                        }
                        return array;
                    "));

                    // ?????????????????????
                    $next_month = $page->evaluate(JsFunction::createWithBody("
                        var next_divs = document.getElementsByTagName('div');
                        var next_month = next_divs[43].innerText

                        return next_month;
                    "));

                    foreach ($next_dates as $next_date) {
                        // "2021???4???21???"??????????????????
                        $next_kanou = $next_month . $next_date . '???';
                        // $???????????????
                        $next_datetime = DateTime::createFromFormat('Y???m???d???', $next_kanou);
                        $next_date_format = $next_datetime->format('Y-m-d');
                        // $time????????????
                        $next_time = '12:34:00';

                        // ???????????????????????????
                        Reservation::create([
                            'date' => $next_date_format,
                            'store_id' =>  $store->id,
                            'time' => $next_time
                        ]);
                    }
                }
                break;
            } while ($right != null);


            foreach ($dates as $date) {
                // "2021???4???21???"??????????????????
                $kanou = $month . $date . '???';
                // DB???????????????????????????????????????
                $datetime = DateTime::createFromFormat('Y???m???d???', $kanou);
                $date_format = $datetime->format('Y-m-d');
                // $time????????????
                $time = '12:34:00';


                Reservation::create([
                    'date' => $date_format,
                    'store_id' =>  $store->id,
                    'time' => $time
                ]);
            }
        }


        printf('Dimensions: %s', print_r($yoyaku, true));


        $browser->close();
    }
}
