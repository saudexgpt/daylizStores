<template>
  <div class="hide-mobile">
    <div style="border: solid 2px #cccccc; border-radius: 5px; padding-left: 10px;">
      <el-collapse v-model="activeName" accordion>
        <el-collapse-item name="1">
          <span slot="title"><label>All Categories</label></span>
          <div v-for="(category, index) in categories" :key="index">
            <div style="cursor: pointer;" @click="loadPage('CategorizedItems', { categoryId: category.id})">
              <hr style="margin-top: 10px; margin-bottom: 10px;">
              {{ category.name }}
            </div>
          </div>
        </el-collapse-item>
      </el-collapse>
    </div>
    <div v-if="showLatestProduct">
      <div class="wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
        <h4 class="section-title-footer ff-secondary text-primary-custom fw-normal">Latest Products</h4>
      </div>
      <div v-for="(stock, stock_index) in latestProducts" :key="stock_index">
        <el-row>
          <div style="cursor: pointer;" @click="loadPage('ProductDetails', { slug: stock.item.slug })">

            <el-col :lg="8" :md="8">
              <img v-if="stock.item.media.length > 0" :src="stock.item.media[0].thumbnail" class="item-image">
              <img v-else src="/images/no-image.jpeg" class="item-image">
            </el-col>
            <el-col :lg="16" :md="16">
              <span>
                {{ stock.item.name }}<br>
                <label>{{ '₦' + formatNumber(stock.item.price.amount, 2) }}</label>
              </span>
            </el-col>
          </div>
        </el-row>
        <br>
      </div>
    </div>
  </div>
</template>
<script>
import { formatNumber } from '@/utils/index';
export default {
  props: {
    showLatestProduct: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      activeName: '1',
    };
  },
  computed: {
    categories() {
      return this.$store.getters.categories;
    },
    latestProducts() {
      return this.$store.getters.latestProducts;
    },
  },
  methods: {
    formatNumber,
    loadPage(name, param) {
      this.$router.push({ name, params: param });
    },
  },
};
</script>
