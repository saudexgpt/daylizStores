<template>
  <div class="app-container">
    <add-new v-if="page.option== 'add_new' || page.option=='edit_item'" :categories="categories" :page="page" :items="items" :params="params" :item="item" @update="onEditUpdate" />
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
              <!-- <a class="btn btn-success" @click="item=props.row; selected_row_index=props.index; page.option = 'edit_item'"><i class="fa fa-level-up" /> </a> -->
              <el-popover
                placement="right"
                width="400"
                trigger="click"
              >
                <div>
                  <strong>Fill the form to stock up {{ props.row.name }}</strong>
                  <hr>
                  <label for="">Color (if applicable)</label>
                  <el-select
                    v-model="stockUpForm.color"
                    filterable
                    placeholder="Select Color"
                    style="width: 100%"
                  >
                    <el-option
                      v-for="(color, index) in colors"
                      :key="index"
                      :value="index"
                      :label="index"
                    >
                      <span :style="`background: ${index}; padding: 5px`">{{ index }}</span>
                    </el-option>
                  </el-select>
                  <!-- <el-input v-model="stockUpForm.color" placeholder="Color" style="width: 100%" /><br> -->
                  <label for="">Size (if applicable)</label>
                  <el-input v-model="stockUpForm.size" placeholder="Size" style="width: 100%" /><br>
                  <label for="">Enter quantity to add</label>
                  <el-input v-model="stockUpForm.quantity" type="number" placeholder="Quantity" style="width: 100%" />
                  <hr>
                  <el-button :disabled="stockUpForm.quantity === ''" type="primary" @click="stockUp(props.row)">Submit</el-button>
                </div>
                <el-button :id="props.row.id" slot="reference" type="success" circle><i class="el-icon-folder-add" /></el-button>
              </el-popover>
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
// import EditItem from './partials/EditItem';
import Resource from '@/api/resource';
// import { colors } from '@/utils/colors';
const necessaryParams = new Resource('fetch-necessary-params');
const itemCategory = new Resource('stock/item-category');
const generalProducts = new Resource('stock/general-items');
const deleteGeneralProducts = new Resource('stock/general-items/delete');
export default {
  components: { Pagination, AddNew },
  props: {
    canCreateNewProduct: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      colors: {
        AliceBlue: '#F0F8FF',
        AntiqueWhite: '#FAEBD7',
        Aqua: '#00FFFF',
        Aquamarine: '#7FFFD4',
        Azure: '#F0FFFF',
        Beige: '#F5F5DC',
        Bisque: '#FFE4C4',
        Black: '#000000',
        BlanchedAlmond: '#FFEBCD',
        Blue: '#0000FF',
        BlueViolet: '#8A2BE2',
        Brown: '#A52A2A',
        BurlyWood: '#DEB887',
        CadetBlue: '#5F9EA0',
        Chartreuse: '#7FFF00',
        Chocolate: '#D2691E',
        Coral: '#FF7F50',
        CornflowerBlue: '#6495ED',
        Cornsilk: '#FFF8DC',
        Crimson: '#DC143C',
        Cyan: '#00FFFF',
        DarkBlue: '#00008B',
        DarkCyan: '#008B8B',
        DarkGoldenRod: '#B8860B',
        DarkGray: '#A9A9A9',
        DarkGrey: '#A9A9A9',
        DarkGreen: '#006400',
        DarkKhaki: '#BDB76B',
        DarkMagenta: '#8B008B',
        DarkOliveGreen: '#556B2F',
        DarkOrange: '#FF8C00',
        DarkOrchid: '#9932CC',
        DarkRed: '#8B0000',
        DarkSalmon: '#E9967A',
        DarkSeaGreen: '#8FBC8F',
        DarkSlateBlue: '#483D8B',
        DarkSlateGray: '#2F4F4F',
        DarkSlateGrey: '#2F4F4F',
        DarkTurquoise: '#00CED1',
        DarkViolet: '#9400D3',
        DeepPink: '#FF1493',
        DeepSkyBlue: '#00BFFF',
        DimGray: '#696969',
        DimGrey: '#696969',
        DodgerBlue: '#1E90FF',
        FireBrick: '#B22222',
        FloralWhite: '#FFFAF0',
        ForestGreen: '#228B22',
        Fuchsia: '#FF00FF',
        Gainsboro: '#DCDCDC',
        GhostWhite: '#F8F8FF',
        Gold: '#FFD700',
        GoldenRod: '#DAA520',
        Gray: '#808080',
        Grey: '#808080',
        Green: '#008000',
        GreenYellow: '#ADFF2F',
        HoneyDew: '#F0FFF0',
        HotPink: '#FF69B4',
        IndianRed: '#CD5C5C',
        Indigo: '#4B0082',
        Ivory: '#FFFFF0',
        Khaki: '#F0E68C',
        Lavender: '#E6E6FA',
        LavenderBlush: '#FFF0F5',
        LawnGreen: '#7CFC00',
        LemonChiffon: '#FFFACD',
        LightBlue: '#ADD8E6',
        LightCoral: '#F08080',
        LightCyan: '#E0FFFF',
        LightGoldenRodYellow: '#FAFAD2',
        LightGray: '#D3D3D3',
        LightGrey: '#D3D3D3',
        LightGreen: '#90EE90',
        LightPink: '#FFB6C1',
        LightSalmon: '#FFA07A',
        LightSeaGreen: '#20B2AA',
        LightSkyBlue: '#87CEFA',
        LightSlateGray: '#778899',
        LightSlateGrey: '#778899',
        LightSteelBlue: '#B0C4DE',
        LightYellow: '#FFFFE0',
        Lime: '#00FF00',
        LimeGreen: '#32CD32',
        Linen: '#FAF0E6',
        Magenta: '#FF00FF',
        Maroon: '#800000',
        MediumAquaMarine: '#66CDAA',
        MediumBlue: '#0000CD',
        MediumOrchid: '#BA55D3',
        MediumPurple: '#9370DB',
        MediumSeaGreen: '#3CB371',
        MediumSlateBlue: '#7B68EE',
        MediumSpringGreen: '#00FA9A',
        MediumTurquoise: '#48D1CC',
        MediumVioletRed: '#C71585',
        MidnightBlue: '#191970',
        MintCream: '#F5FFFA',
        MistyRose: '#FFE4E1',
        Moccasin: '#FFE4B5',
        NavajoWhite: '#FFDEAD',
        Navy: '#000080',
        OldLace: '#FDF5E6',
        Olive: '#808000',
        OliveDrab: '#6B8E23',
        Orange: '#FFA500',
        OrangeRed: '#FF4500',
        Orchid: '#DA70D6',
        PaleGoldenRod: '#EEE8AA',
        PaleGreen: '#98FB98',
        PaleTurquoise: '#AFEEEE',
        PaleVioletRed: '#DB7093',
        PapayaWhip: '#FFEFD5',
        PeachPuff: '#FFDAB9',
        Peru: '#CD853F',
        Pink: '#FFC0CB',
        Plum: '#DDA0DD',
        PowderBlue: '#B0E0E6',
        Purple: '#800080',
        RebeccaPurple: '#663399',
        Red: '#FF0000',
        RosyBrown: '#BC8F8F',
        RoyalBlue: '#4169E1',
        SaddleBrown: '#8B4513',
        Salmon: '#FA8072',
        SandyBrown: '#F4A460',
        SeaGreen: '#2E8B57',
        SeaShell: '#FFF5EE',
        Sienna: '#A0522D',
        Silver: '#C0C0C0',
        SkyBlue: '#87CEEB',
        SlateBlue: '#6A5ACD',
        SlateGray: '#708090',
        SlateGrey: '#708090',
        Snow: '#FFFAFA',
        SpringGreen: '#00FF7F',
        SteelBlue: '#4682B4',
        Tan: '#D2B48C',
        Teal: '#008080',
        Thistle: '#D8BFD8',
        Tomato: '#FF6347',
        Turquoise: '#40E0D0',
        Violet: '#EE82EE',
        Wheat: '#F5DEB3',
        White: '#FFFFFF',
        WhiteSmoke: '#F5F5F5',
        Yellow: '#FFFF00',
        YellowGreen: '#9ACD32',
      },
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
        sortable: ['name', 'category.name', 'quantity'],
        filterable: ['name', 'category.name'],
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
        balance += (stock.quantity_stocked - stock.sold);
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
    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        const load = deleteGeneralProducts.loaderShow();
        deleteGeneralProducts.destroy(row.id, row)
          .then(response => {
            app.items.splice(row.index - 1, 1);
            this.$message({
              message: 'Item has been deleted',
              type: 'success',
            });
            load.hide();
          })
          .catch(error => {
            load.hide();
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
