<?php

namespace Database\Seeders;

use App\Models\HomepageBlock;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            ['assurance','compare-fares','Compare fares','Every provider offer, side by side',null,null,'list',null,null,[],10,true],
            ['assurance','live-revalidation','Live revalidation','Selected fares are rechecked',null,null,'check',null,null,[],20,true],
            ['assurance','secure-account','Secure account steps','Search and booking stay protected',null,null,'shield',null,null,[],30,true],
            ['assurance','visible-status','Status you can check','Booking status remains visible',null,null,'document',null,null,[],40,true],
            ['assurance','honest-availability','Honest availability','Services appear only when configured',null,null,'globe',null,null,[],50,true],

            ['promotion','flight-offer','FLAT 20% OFF on International Flights','Compare current fares from the configured flight provider.',null,'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1400&q=85','plane',null,'Search Flights',['badge'=>'Flights','feature'=>'flights'],10,true],
            ['promotion','hotel-offer','Up to 40% OFF on Hotel Bookings','Explore hotel options when the hotel provider is configured.',null,'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=85','hotel',null,'Book Now',['badge'=>'Hotels','feature'=>'hotels'],20,true],
            ['promotion','work-visa-promo','Work Visa Processing','Start your global career with work visa information and document guidance.',null,'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=85','briefcase',null,'Apply Now',['badge'=>'Work Visa','work_visa'=>true,'items'=>['Job Visa','Skilled Worker Visa','Employer Sponsored Visa','Document Assistance']],30,true],

            ['service','flights','Flights','Domestic & International',null,null,'plane',null,null,['service'=>'flights'],10,true],
            ['service','hotels','Hotels','Best stays worldwide',null,null,'hotel',null,null,['service'=>'hotels'],20,true],
            ['service','tours','Tours & Activities','Explore experiences',null,null,'globe',null,null,['service'=>'tours'],30,true],
            ['service','visa','Visa Assistance','Tourist & Business Visa',null,null,'document',null,null,['service'=>'visa'],40,true],
            ['service','work-visa','Work Visa Processing','Jobs & Work Permits',null,null,'briefcase',null,null,['service'=>null],50,true],
            ['service','holiday-packages','Holiday Packages','Customized tours',null,null,'gift',null,null,['service'=>null],60,true],
            ['service','trains','Trains','Easy train booking',null,null,'train',null,null,['service'=>null],70,true],
            ['service','buses','Buses','Comfortable travel',null,null,'bus',null,null,['service'=>null],80,true],
            ['service','cabs','Cabs','Airport & local rides',null,null,'car',null,null,['service'=>null],90,true],
            ['service','insurance','Travel Insurance','Safe & secure journeys',null,null,'shield',null,null,['service'=>null],100,true],

            ['destination','dubai','Dubai','United Arab Emirates',null,'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'UAE'],10,true],
            ['destination','bali','Bali','Indonesia',null,'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'Indonesia'],20,true],
            ['destination','bangkok','Bangkok','Thailand',null,'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'Thailand'],30,true],
            ['destination','singapore','Singapore','Singapore',null,'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'Singapore'],40,true],
            ['destination','maldives','Maldives','Maldives',null,'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'Maldives'],50,true],
            ['destination','london','London','United Kingdom',null,'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'UK'],60,true],
            ['destination','istanbul','Istanbul','Türkiye',null,'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'Türkiye'],70,true],
            ['destination','paris','Paris','France',null,'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1000&q=85',null,null,null,['tag'=>'France'],80,true],

            ['service_panel','visa-services','Visa Services','Get visa assistance for your destination.','Tourist, business and transit visa support.','https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=85','document',null,'Apply for Visa',['class'=>'is-visa','service'=>'visa','items'=>['Tourist Visa','Business Visa','Transit Visa','Visa Requirements Check']],10,true],
            ['service_panel','work-visa','Work Visa Processing','Your Global Career Starts Here','Work permit and document assistance.','https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=85','briefcase',null,'Apply Now',['class'=>'is-workvisa','service'=>null,'work_visa'=>true,'items'=>['Job Visa & Work Permits','Skilled Worker Visa','Employer Sponsored Visa','Document Assistance']],20,true],
            ['service_panel','holiday-packages','Holiday Packages','Explore amazing tour packages','Holiday package presentation content.','https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85','gift',null,'View Packages',['class'=>'is-holiday','service'=>null,'items'=>['Family Packages','Honeymoon Packages','Adventure Tours','Customized Itineraries']],30,true],

            ['benefit','compare','Fares compared in one place','Every offer returned by the configured provider is listed side by side.',null,null,'list',null,null,[],10,true],
            ['benefit','revalidate','Live fare revalidation','A selected fare is revalidated before traveller details are collected.',null,null,'check',null,null,[],20,true],
            ['benefit','account','Secure account steps','Search, traveller and booking status stay inside the signed-in account.',null,null,'shield',null,null,[],30,true],
            ['benefit','availability','Explicit availability','A service is marked available only when its provider is configured.',null,null,'globe',null,null,[],40,true],
            ['benefit','status','Visible booking status','Bookings keep their own order and payment status for later review.',null,null,'document',null,null,[],50,true],
            ['benefit','credentials','Credentials stay server-side','Provider credentials are never rendered into public pages.',null,null,'lock',null,null,[],60,true],

            ['testimonial','sample-rahim','Rahim Uddin','Work Visa Customer','Excellent service! Got my work visa processed smoothly through Eagle Global Hub.',null,'user',null,null,['verified'=>false],10,false],
            ['testimonial','sample-nusrat','Nusrat Jahan','Flight Booking Customer','Great flight deals and amazing support throughout the booking process.',null,'user',null,null,['verified'=>false],20,false],
            ['testimonial','sample-tanvir','Tanvir Ahmed','Tour Package Customer','Very professional and trustworthy. Highly recommended!',null,'user',null,null,['verified'=>false],30,false],
        ];

        foreach ($blocks as [$section,$key,$title,$subtitle,$body,$image,$icon,$url,$cta,$meta,$sort,$active]) {
            HomepageBlock::query()->updateOrCreate(
                ['section'=>$section,'key'=>$key],
                ['title'=>$title,'subtitle'=>$subtitle,'body'=>$body,'image_path'=>$image,'image_alt'=>$title,'icon'=>$icon,'url'=>$url,'cta_label'=>$cta,'meta'=>$meta,'sort_order'=>$sort,'is_active'=>$active],
            );
        }
    }
}
