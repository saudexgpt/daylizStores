<template>
  <div class="app-container">
    <add-new v-if="page.option== 'add_new' || page.option=='edit_item'" :categories="categories" :page="page" :items="items" :params="params" :item="item" @update="onEditUpdate" />
    <stock-item v-if="page.option=='stock_item'" :page="page" :item="item" @update="onEditUpdate" />
    <!-- <edit-item v-if="page.option=='edit_item'" :categories="categories" :page="page" :item="item" :params="params" @update="onEditUpdate" /> -->
    <div v-if="page.option=='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">List of Products</h4>
        <span class="pull-right">
          <a v-if="canCreateNewProduct" class="btn btn-info" @click="page.option = 'add_new'"> Add New</a>
        </span>
      </div>
      <div class="box-body">
        <div style="margin:0 0 20px 20px;">
          <el-select v-model="query.category_id" placeholder="Select item category" filterable style="width: 100%" @input="fetchGeneralProducts">
            <el-option v-for="(category, index) in categories" :key="index" :value="category.id" :label="category.name" />

          </el-select>
          <!-- <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            Export Excel
          </el-button> -->
        </div>
        <aside>
          <el-row>
            <el-col :xs="24" :sm="24" :md="24">
              <el-input v-model="query.item_name" placeholder="Search Item" class="no-border">
                <el-button slot="append" :disabled="item_name === ''" type="primary" icon="el-icon-search" @click="fetchGeneralProducts" />
              </el-input>
            </el-col>
          </el-row>
        </aside>
        <v-client-table v-model="items" v-loading="load" :columns="columns" :options="options">
          <div slot="picture" slot-scope="{row}">
            <img :src="row.picture" alt="Image" width="100">
          </div>
          <div slot="category.name" slot-scope="{row}">
            {{ row.category.name }}
          </div>
          <div slot="price.amount" slot-scope="{row}">
            <span align="right">{{ '₦' + Number(row.price.amount).toLocaleString() }}</span>
          </div>
          <div slot="quantity" slot-scope="{row}">
            <span align="right">{{ calculateBalance(row.item_stocks) }}</span>
          </div>
          <div slot="action" slot-scope="props">
            <el-tooltip content="Edit" placement="top">
              <a class="btn btn-primary" @click="item=props.row; selected_row_index=props.index; page.option = 'edit_item'"><i class="fa fa-edit" /> </a>
            </el-tooltip>
            <el-tooltip content="Stock Up" placement="top">
              <a class="btn btn-success" @click="item=props.row; selected_row_index=props.index; page.option = 'stock_item'"><i class="el-icon-coin" /> </a>
            </el-tooltip>
            <el-tooltip v-if="props.row.enabled === 0" content="Enable" placement="top">

              <a class="btn btn-info" @click="toggleStatus(props.row, 'enabled', 1)"><i class="el-icon-check" /> </a>
            </el-tooltip>
            <el-tooltip v-else content="Disable" placement="top">

              <a class="btn btn-danger" @click="toggleStatus(props.row, 'disabled', 0)"><i class=" el-icon-close" /> </a>
            </el-tooltip>

            <!-- <a class="btn btn-primary" @click="item=props.row; selected_row_index=props.index; page.option = 'edit_item'"><i class="fa fa-edit" /> </a>
            <a class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash" /> </a> -->
          </div>

        </v-client-table>
        <el-row :gutter="20">
          <pagination
            v-show="total > 0"
            :total="total"
            :page.sync="query.page"
            :limit.sync="query.limit"
            @pagination="fetchGeneralProducts"
          />
        </el-row>

      </div>

    </div>

  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import AddNew from './partials/AddNew';
import StockItem from './partials/StockItem';
import Resource from '@/api/resource';
// import { colors } from '@/utils/colors';
const necessaryParams = new Resource('fetch-necessary-params');
const itemCategory = new Resource('stock/item-category');
const generalProducts = new Resource('stock/general-items');
const deleteGeneralProducts = new Resource('stock/general-items/delete');
export default {
  components: { Pagination, AddNew, StockItem },
  props: {
    canCreateNewProduct: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      categories: [],
      items: [],
      columns: ['action', 'name', 'category.name', /* 'package_type',*/ 'price.amount', 'quantity'],

      options: {
        headings: {
          name: 'Name',
          'category.name': 'Category',
          quantity: 'Quantity Remaining',
          'price.amount': 'Rate',
          // id: 'S/N',
        },
        filterByColumn: true,
        perPage: 10,
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: [],
        filterable: [],
      },
      page: {
        option: 'list',
      },
      item: {

      },
      params: [],
      selected_row_index: '',
      downloadLoading: false,
      stockUpForm: {
        color: null,
        size: null,
        quantity: '',
      },
      load: false,
      query: {
        page: 1,
        limit: 10,
        category_id: '',
        item_name: '',
      },
      total: 0,

    };
  },

  mounted() {
    // this.fetchGeneralProducts();
    this.fetchNecessaryParams();
    this.fetchCategories();
  },
  beforeDestroy() {

  },
  methods: {
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
        });
    },
    fetchCategories() {
      const app = this;
      // let loader = Vue.$loading.show({});
      // const load = itemCategory.loaderShow();
      const param = app.form;
      itemCategory.list(param)
        .then(response => {
          app.categories = response.categories;
          // load.hide();
        });
    },
    fetchGeneralProducts() {
      const { limit, page } = this.query;
      this.options.perPage = limit;
      this.load = true;
      generalProducts
        .list(this.query)
        .then((response) => {
          this.items = response.items.data;
          this.items.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          this.total = response.items.total;
          this.load = false;
        })
        .catch((error) => {
          console.log(error);
          this.load = false;
        });
    },
    onEditUpdate(updated_row) {
      const app = this;
      app.page.option = 'list';
      // app.items_in_stock.splice(app.itemInStock.index-1, 1);
      app.items[app.selected_row_index - 1] = updated_row;
    },
    calculateBalance(stocks) {
      let balance = 0;
      stocks.forEach(stock => {
        balance += (stock.quantity_stocked - stock.reserved - stock.sold);
      });
      return balance;
    },
    stockUp(item) {
      const app = this;
      document.getElementById(item.id).click();
      app.load = true;
      const stockUpProducts = new Resource('stock/general-items/stockup');
      stockUpProducts.update(item.id, app.stockUpForm)
        .then(response => {
          app.fetchGeneralProducts();
          app.stockUpForm = {
            color: null,
            size: null,
            quantity: '',
          };
          app.load = false;
        })
        .catch(error => {
          app.load = false;
          console.log(error);
        });
    },
    toggleStatus(row, action, value) {
      // this.loader();

      const param = { action, value };
      const app = this;
      const message = `Click OK to confirm that you want to change product to ${action}`;
      if (confirm(message)) {
        // const load = deleteGeneralProducts.loaderShow();
        app.load = true;
        const toggleStatusResource = new Resource('stock/general-items/toggle-status');
        toggleStatusResource.update(row.id, param)
          .then(response => {
            app.fetchGeneralProducts();
            this.$message({
              message: 'Action Successful',
              type: 'success',
            });
            app.load = false;
          })
          .catch(error => {
            app.load = false;
            console.log(error);
          });
      }
    },
    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        // const load = deleteGeneralProducts.loaderShow();
        app.load = true;
        deleteGeneralProducts.destroy(row.id, row)
          .then(response => {
            app.items.splice(row.index - 1, 1);
            this.$message({
              message: 'Item has been deleted',
              type: 'success',
            });
            app.load = false;
          })
          .catch(error => {
            app.load = false;
            console.log(error);
          });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        // const multiHeader = [['List of Products', '', '', '', '']];
        const tHeader = ['PRODUCT', 'CATEGORY', 'PACKAGE_TYPE', 'RATE'];
        const filterVal = ['name', 'category.name', 'package_type', 'price.amount'];
        const list = this.items;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          // multiHeader,
          header: tHeader,
          data,
          filename: 'Product-List',
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'category.name') {
          return v['category']['name'];
        } else {
          if (j === 'price.amount') {
            if (v['price']){
              return v['price']['amount'];
            } else {
              return '0';
            }
          }
        }
        return v[j];
      }));
    },
  },
};
</script>
