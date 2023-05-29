<template>
  <div>
    <el-carousel :interval="7000">
      <el-carousel-item v-for="(item, index) in items" :key="index">
        <aside class="writup">
          <el-row :gutter="20">
            <el-col :xs="12" :sm="12" :md="12">
              <h1 class="title">{{ item.title }}</h1>
              <p class="description">{{ item.description }}</p>
              <el-button type="primary" round @click="loadPage('/product/list')">Shop Now</el-button>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12">
              <img :src="item.url" style="width: 100%">
            </el-col>
          </el-row>
        </aside>
      </el-carousel-item>
    </el-carousel>

    <!-- <about-us /> -->
    <item-menu :category-id="categoryId" />
  </div>
</template>
<script>
import ItemMenu from './Menu';
// import Resource from '@/api/resource';
export default {
  name: 'Home',
  components: {
    ItemMenu,
  },
  data() {
    return {
      categoryId: null,
      searchString: '',
      items: [
        { url: '/images/slider1.png', title: 'Household Items', description: 'Get your quality household items at affordable prices' },
        { url: '/images/slider2.png', title: 'Souvenirs', description: 'Varieties of gift items and souvenirs at affordable prices' },
        { url: '/images/slider6.png', title: 'Kiddies', description: 'Varieties of quality products for kids' },
        { url: '/images/slider4.png', title: 'Men\'s Fashion', description: 'Quality products for men at affordable prices' },
        { url: '/images/slider5.png', title: 'Ladies\' Fashion', description: 'Quality products for ladies at affordable prices' },
        { url: '/images/slider3.png', title: 'Buy Online', description: 'You are just a click away from grabbing your desired product' },
      ],
    };
  },
  computed: {
    categories() {
      return this.$store.getters.categories;
    },
  },
  methods: {
    loadPage(path) {
      this.$router.push({ path });
    },
  },
};
</script>
<style>
  .el-carousel__item h3 {
    color: #475669;
    font-size: 18px;
    opacity: 1;
    margin: 0;
    background-color: transparent;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: transparent;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: transparent;
  }
</style>
