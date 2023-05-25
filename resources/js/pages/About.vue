<template>
  <div class="app-container" style="background: #f0f0f0;">
    <el-row :gutter="20">
      <div>
        <el-col
          :lg="12"
          :md="12"
          :sm="12"
          :xs="24"
        >

          <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
            <h2 class="section-title ff-secondary text-center text-primary-custom fw-normal">About Us</h2>

            <h3 class="mb-4">Welcome to</h3>
            <img class="img-fluid rounded w-75 wow zoomIn" data-wow-delay="0.3s" src="/images/logo-with-bg.jpg" width="250">
            <h4>We are now open to your Orders!!!</h4>

            <h4>Please view our <router-link to="/product/list">shop</router-link> to make an order</h4>
          </div>
        </el-col>

        <el-col
          :lg="12"
          :md="12"
          :sm="12"
          :xs="24"
        >
          <h3 class="section-title-footer ff-secondary text-center text-primary-custom fw-normal mb-4">Contact</h3>
          <p class="mb-2"><i class="fa fa-map-marker-alt me-3" />
            Shop D13, Ayobo garage complex,<br>
            Ayobo garage after Megida bus stop,<br>
            Ayobo-Ipaja,<br>
            Lagos, Nigeria
          </p>
          <p class="mb-2"><i class="fa fa-phone-alt me-3" /> +234 814 687 5777 | +234 913 687 7689</p>
          <p class="mb-2"><i class="fa fa-envelope me-3" /> info@daylizstores.com</p>
          <p class="mb-2"><i class="fab fa-instagram me-3" /> @dayliz_stores</p>
          <p class="mb-2"><a href="https://web.facebook.com/daylizcollections" target="_blank"><i class="fab fa-facebook me-3" /> Dayliz Collections</a></p>
          <p class="mb-2"><a
            href="https://web.whatsapp.com/send?phone=+2348146875777"
            target="_blank"
          ><i class="fab fa-whatsapp me-3" /> Message us via WhatsApp</a></p>
        </el-col>
      </div>
    </el-row>
  </div>
</template>
<script>
// import Resource from '@/api/resource';
export default {
  name: 'Trackorder',
  data() {
    return {
      img: '/images/logo.png',
    };
  },
  methods: {
  },
};
</script>
