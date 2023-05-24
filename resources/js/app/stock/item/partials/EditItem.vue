<template>
  <div class="box">
    <div class="box-header">
      <h4 class="box-title">Edit Item</h4>
      <span class="pull-right">
        <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
      </span>
    </div>
    <div class="box-body">
      <aside>
        <el-form ref="form" :model="form" label-width="120px">
          <el-row :gutter="10" class="padded">
            <el-col :xs="24" :sm="12" :md="12">
              <label for="">Select Category</label>
              <el-select v-model="form.category_id" placeholder="Select item category" filterable style="width: 100%">
                <el-option v-for="(category, index) in categories" :key="index" :value="category.id" :label="category.name" />

              </el-select>
              <label for="">Name of Item</label>
              <el-input v-model="form.name" placeholder="Enter item name" class="span" />
              <label for="">Description</label>
              <textarea v-model="form.description" placeholder="Briefly describe product" rows="3" class="form-control" />
              <div>
                <img v-for="(itemMedia, index) in form.media" :key="index" :src="itemMedia.thumbnail" width="100" style="padding: 10px 5px 0 0; cursor: pointer" @click="selectedImg = itemMedia.link">
              </div>
              <el-upload
                ref="upload"
                class="upload-demo"
                action="/api/upload-file"
                :headers="headers"
                :before-upload="beforeAvatarUpload"
                :on-success="handleAvatarSuccess"
                :on-remove="handleRemove"
                :before-remove="beforeRemove"
                multiple
                :limit="3"
                :on-exceed="handleExceed"
                :file-list="fileList"
                list-type="picture-card"
              >
                <i class="el-icon-plus avatar-uploader-icon" />
              </el-upload>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12">
              <label for="">Rate per item</label>
              <el-input v-model="form.amount" placeholder="Price">
                <template slot="prepend">₦</template>
              </el-input>
              <h4>MOQ Discounts</h4>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th />
                    <th>MOQ</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(discount, index) in form.discounts" :key="index">
                    <td>
                      <span>
                        <a
                          class="btn btn-danger btn-flat fa fa-trash"
                          @click="removeLine(index)"
                        />
                        <a
                          v-if="index + 1 === discounts.length"
                          class="btn btn-info btn-flat fa fa-plus"
                          @click="addLine(index)"
                        />
                      </span>
                    </td>
                    <td>
                      <el-input
                        v-model="discount.minimum_order_quantity"
                        type="number"
                        outline
                        placeholder="Minimum Order Quantity"
                        min="10"
                      />
                    </td>
                    <td align="right">
                      <el-input
                        v-model="discount.amount"
                        outline
                        placeholder="Amount"
                      />
                    </td>
                  </tr>
                  <tr v-if="fill_fields_error">
                    <td colspan="5">
                      <label
                        class="label label-danger"
                      >Please fill all empty fields before adding another row</label>
                    </td>
                  </tr>
                </tbody>
              </table>
              <hr>
              <el-button type="success" @click="addProduct"><svg-icon icon-class="edit" />
                Submit
              </el-button>
            </el-col>
          </el-row>
        <!-- <el-row :gutter="2" class="padded">
          <el-col :xs="24" :sm="6" :md="6">
            <el-button type="success" @click="editProduct"><svg-icon icon-class="edit" />
              Update Product
            </el-button>
          </el-col>
        </el-row> -->
        </el-form>
      </aside>
    </div>
  </div>
</template>

<script>

import Resource from '@/api/resource';
const updateProduct = new Resource('stock/general-items/update');
const deleteProductTax = new Resource('stock/general-items/delete-item-tax');
export default {
  name: 'EditProduct',
  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    categories: {
      type: Array,
      default: () => ([]),
    },
    item: {
      type: Object,
      default: () => ([]),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'edit_item',
      }),
    },

  },
  data() {
    return {
      form: {
        name: '',
        quantity_per_carton: '',
        category_id: '',
        description: '',
        currency_id: 1,
        images: [],
        deletedImages: [],
        discounts: [],
        // tax_ids: [],
        // purchase_price: '',
        amount: 0,
        quantity_stocked: 0,

      },
      empty_form: {
        name: '',
        quantity_per_carton: '',
        category_id: '',
        description: '',
        currency_id: 1,
        images: [],
        deletedImages: [],
        discounts: [],
        // tax_ids: [],
        // purchase_price: '',
        amount: 0,
        quantity_stocked: 0,

      },
      item_price: {
        amount: 0,
        currency_id: 1,
      },
      image: '/images/no-image.jpeg',
      imageUrl: '',
      fileList: [],
      tempImages: [],

    };
  },
  mounted() {
    this.setEditForm();
  },
  methods: {
    setEditForm() {
      const app = this;
      app.form = app.item;
      app.item_price = (app.item.price) ? app.item.price : app.item_price;
    },
    cropUploadSuccess(jsonData, field){
      console.log('-------- upload success --------');
      // console.log(jsonData);
      // console.log('field: ' + field);
      const app = this;
      app.imagecropperShow = false;
      app.imagecropperKey = app.imagecropperKey + 1;
      app.form.picture = jsonData.avatar;
    },
    cropUploadFail(status, field){
      console.log('-------- upload fail --------');
      console.log(status);
      console.log('field: ' + field);
    },
    close() {
      this.imagecropperShow = false;
    },
    editProduct() {
      const app = this;
      const load = updateProduct.loaderShow();
      var form = app.form;
      form.currency_id = app.item_price.currency_id;
      // form.purchase_price = app.item_price.purchase_price;
      form.amount = app.item_price.amount;
      updateProduct.update(form.id, form)
        .then(response => {
          app.$message({ message: 'Product Updated Successfully!!!', type: 'success' });
          // app.item = response.item;
          app.$emit('update', response.item);

          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error.message);
        });
    },
    destroyProductTax(index, tax_id) {
      const app = this;
      const param = {
        item_id: app.item.id,
        tax_id: tax_id,
      };
      deleteProductTax.list(param)
        .then(response => {
          // app.$message({ message: 'Product Updated Successfully!!!', type: 'success' });
          app.item.taxes.splice(index, 1);

          // app.$emit('update', response);
        })
        .catch(error => {
          alert(error.message);
        });
    },

  },
};
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.list-complete-item {
  cursor: pointer;
  position: relative;
  font-size: 14px;
  padding: 5px 12px;
  margin-top: 4px;
  border: 1px solid #e9a0a0;
  background: #e9a0a0;
  color: #fff;
  transition: all 1s;
}

.list-complete-item-handle {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-right: 50px;
}
</style>
