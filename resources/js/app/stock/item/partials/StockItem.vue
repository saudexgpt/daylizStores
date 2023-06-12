<template>
  <div class="box">
    <div class="box-header">
      <h4 class="box-title">Stock Up {{ item.name }}</h4>
      <span class="pull-right">
        <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
      </span>
    </div>
    <div v-loading="load" class="box-body">
      <el-row :gutter="2" class="padded">
        <el-col>
          <div style="overflow: auto">
            <table class="table table-binvoiceed">
              <thead>
                <tr>
                  <th />
                  <th>Quantity</th>
                  <th>Color (if applicable)</th>
                  <th>Size (if applicable)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(sub_batch, index) in sub_batches" :key="index">
                  <td>
                    <span>
                      <a v-if="sub_batches.length > 1" class="btn btn-danger btn-flat fa fa-trash" @click="removeLine(index)" />
                      <a class="btn btn-info btn-flat fa fa-plus" @click="addLine(index)" />
                    </span>
                  </td>
                  <td>
                    <el-input v-model="sub_batch.quantity" type="number" outline placeholder="Quantity" />
                  </td>
                  <td>
                    <el-select
                      v-model="sub_batch.color"
                      filterable
                      placeholder="Select Color"
                      style="width: 100%"
                    >
                      <el-option value="others" label="Others" />
                      <el-option
                        v-for="(color, color_index) in params.colors"
                        :key="color_index"
                        :value="color_index"
                        :label="color_index"
                      >
                        <span :style="`background: ${color_index}; color: #000000; padding: 5px 15px 5px 15px`">&nbsp;&nbsp;</span>
                        <span class="pull-right">{{ color_index }}</span>
                      </el-option>
                    </el-select>
                    <el-input v-if="sub_batch.color === 'others'" v-model="sub_batch.other_color" placeholder="Specify color" />
                  </td>
                  <td>
                    <el-input v-model="sub_batch.size" placeholder="Size" style="width: 100%" />
                  </td>
                </tr>
                <tr v-if="fill_fields_error">
                  <td colspan="6"><label class="label label-danger">Please fill all empty fields before adding another row</label></td>
                </tr>
              </tbody>
            </table>
          </div>
        </el-col>
      </el-row>
      <el-row :gutter="2" class="padded">
        <el-col :xs="24" :sm="6" :md="6">
          <el-button type="success" @click="addProductToStock"><i class="el-icon-upload" />
            Submit
          </el-button>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';
export default {
  name: 'StockProduct',
  components: {},
  props: {
    item: {
      type: Object,
      default: () => {},
    },

    page: {
      type: Object,
      default: () => ({
        option: 'stock_item',
      }),
    },

  },
  data() {
    return {
      fill_fields_error: false,
      form: {
        item_id: '',
        sub_batches: [
          {
            quantity: '',
            color: '',
            other_color: '',
            size: '',
          },
        ],

      },
      empty_form: {
        item_id: '',
        sub_batches: [
          {
            quantity: '',
            color: '',
            other_color: '',
            size: '',
          },
        ],

      },
      sub_batches: [],
      load: false,

    };
  },
  computed: {
    params() {
      return this.$store.getters.params;
    },
  },
  watch: {
    sub_batches() {
      this.blockRemoval = this.sub_batches.length <= 1;
    },

  },
  mounted() {
    this.addLine();
  },
  methods: {
    moment,
    emptyField() {
      const app = this;
      const checkEmptyLines = app.sub_batches.filter(detail => detail.quantity === '');
      if (checkEmptyLines.length > 0) {
        return true;
      }
      return false;
    },
    addLine(index) {
      const app = this;
      this.fill_fields_error = false;

      if (app.emptyField() && this.sub_batches.length > 0) {
        this.fill_fields_error = true;
        // this.sub_batches[index].seleted_category = true;
        return;
      } else {
        // if (this.sub_batches.length > 0)
        //     this.sub_batches[index].grade = '';

        this.sub_batches.push({
          quantity: '',
          color: '',
          other_color: '',
          size: '',
        });
      }
    },
    removeLine(detailId) {
      this.fill_fields_error = false;
      if (!this.blockRemoval) {
        this.sub_batches.splice(detailId, 1);
      }
    },
    addProductToStock() {
      const app = this;
      if (app.emptyField()) {
        app.$alert('Kindly fill all empty fields');
        return;
      }
      app.load = true;
      const form = app.form;
      form.item_id = app.item.id;
      form.sub_batches = app.sub_batches;
      const stockUpProducts = new Resource('stock/general-items/stockup');
      stockUpProducts.update(app.item.id, app.form)
        .then(response => {
          app.form = app.empty_form;
          app.sub_batches = [{
            quantity: '',
            color: '',
            size: '',
          }];
          app.$message({ message: 'Product Added Successfully!!!', type: 'success' });
          app.$emit('update', response.item);
          app.load = false;
        })
        .catch(error => {
          app.load = false;
          alert(error.message);
        });
    },

  },
};
</script>

