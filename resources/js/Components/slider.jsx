import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

export default function Slider() {

  const items = 4;

  if (items < 1) return null;

  return (
    <Swiper
        modules={[Navigation, Pagination]}
        spaceBetween={16}
        navigation
        pagination={{ clickable: true }}
        loop={items > 3}
        slidesPerView={1} 
        breakpoints={{
            640: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            1024: { slidesPerView: 3 },
        }}
        className="w-full"
        >
      {/* Card 1 */}
      <SwiperSlide>
        <div className="bg-[url(img/static/mundial6.png)] bg-cover bg-no-repeat h-48 rounded-2xl border-4 border-green-400">
          <div className="flex flex-col justify-between h-full p-6">
            <span className="text-white text-4xl">
              Mundial <br /> Kalea
            </span>
            <span className="text-white text-xs">Creado por:</span>
          </div>
        </div>
      </SwiperSlide>

      {/* Card 2 */}
      <SwiperSlide>
        <div className="bg-[url(img/static/eurocopa.jpg)] bg-cover bg-no-repeat h-48 rounded-2xl">
          <div className="flex flex-col justify-between h-full p-6">
            <span className="text-white text-5xl">Kalea</span>
            <span className="text-white">Creado por:</span>
          </div>
        </div>
      </SwiperSlide>

      {/* Card 3 */}
      <SwiperSlide>
        <div className="bg-[url(img/static/eurocopa.jpg)] bg-cover bg-no-repeat h-48 rounded-2xl">
          <div className="flex flex-col justify-between h-full p-6">
            <span className="text-white text-5xl">Kalea</span>
            <span className="text-white">Creado por:</span>
          </div>
        </div>
      </SwiperSlide>

      {/* Card 4 */}
      <SwiperSlide>
        <div className="bg-[url(img/static/mundial6.png)] bg-cover bg-no-repeat h-48 rounded-2xl">
          <div className="flex flex-col justify-between h-full p-6">
            <span className="text-white text-4xl">Otro</span>
            <span className="text-white text-xs">Creado por:</span>
          </div>
        </div>
      </SwiperSlide>
    </Swiper>
  );
}
