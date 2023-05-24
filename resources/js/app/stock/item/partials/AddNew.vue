<template>
  <div class="box">
    <div class="box-header">
      <h4 v-if="page.option === 'add_new'" class="box-title">Add Product</h4>
      <h4 v-if="page.option === 'edit_item'" class="box-title">Edit Product</h4>
      <span class="pull-right">
        <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
      </span>
    </div>
    <div v-loading="load" class="box-body">
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
            <div v-if="media.length > 0">
              <h4>Click on image to delete</h4><br>
              <img v-for="(itemMedia, img_index) in media" :key="img_index" :src="itemMedia.thumbnail" width="150" style="padding: 10px 5px 0 0; cursor: pointer;" @click="removeSavedImage(itemMedia.id, img_index)">
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
                <tr v-for="(discount, index) in discounts" :key="index">
                  <td>
                    <span>
                      <a
                        class="btn btn-danger btn-flat fa fa-trash"
                        @click="removeLine(discount, index)"
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
            <el-button v-if="page.option === 'add_new'" type="success" @click="addProduct"><svg-icon icon-class="edit" />
              Submit
            </el-button>
            <el-button v-if="page.option === 'edit_item'" type="warning" @click="editProduct"><svg-icon icon-class="edit" />
              Update Product
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

    </div>
  </div>
</template>

<script>
// import ImageCropper from '@/components/ImageCropper';
import { getToken } from '@/utils/auth';
import Resource from '@/api/resource';
const createProduct = new Resource('stock/general-items/store');

export default {
  name: 'AddNewProduct',
  // components: { ImageCropper },
  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    categories: {
      type: Array,
      default: () => ([]),
    },
    items: {
      type: Array,
      default: () => ([]),
    },
    item: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'add_new',
      }),
    },

  },
  data() {
    return {
      headers: {
        Authorization: 'Bearer ' + getToken(),
      },
      form: {
        name: '',
        package_type: 'Plate',
        quantity_per_carton: '',
        category_id: '',
        description: '',
        picture: '/images/no-image.jpeg',
        currency_id: 1,
        images: [],
        deletedImages: [],
        deletedDiscounts: [],
        discounts: [],
        // tax_ids: [],
        // purchase_price: '',
        amount: 0,
        quantity_stocked: 0,

      },
      media: [],
      load: false,
      discounts: [],
      fill_fields_error: false,
      imagecropperShow: false,
      imagecropperKey: 0,
      image: '/images/no-image.jpeg',
      imageUrl: '',
      fileList: [],
      tempImages: [],
    };
  },
  watch: {
    discounts() {
      this.blockRemoval = this.discounts.length <= 1;
    },
  },
  created() {
    if (this.page.option === 'edit_item') {
      this.setEditForm();
    }
    this.addLine();
  },
  methods: {
    setEditForm() {
      const app = this;
      app.form = app.item;
      app.media = app.item.media;
      app.form.amount = app.item.price.amount;
      app.discounts = app.item.discounts;
      app.form.images = [];
      app.form.deletedImages = [];
      app.form.deletedDiscounts = [];
    },
    addLine() {
      this.fill_fields_error = false;

      const checkEmptyLines = this.discounts.filter(
        (detail) =>
          detail.amount === '' ||
          detail.minimum_order_quantity === '',
      );

      if (checkEmptyLines.length >= 1 && this.discounts.length > 0) {
        this.fill_fields_error = true;
        // this.discounts[index].seleted_category = true;
        return;
      } else {
        // if (this.discounts.length > 0)
        //     this.discounts[index].grade = '';
        this.discounts.push({
          item_id: '',
          minimum_order_quantity: '',
          amount: '',
        });
      }
    },
    removeLine(discount, index) {
      this.fill_fields_error = false;
      if (!this.blockRemoval) {
        this.discounts.splice(index, 1);
        if (discount.id) {
          this.form.deletedDiscounts.push(discount.id);
        }
      }
    },
    handleRemove(file, fileList) {
      // we need to delete this file from the db, so let's keep record of their ids
      this.form.deletedImages.push(file.response.media_id);
    },
    removeSavedImage(mediaId, index) {
      this.$confirm(`Delete this image?`).then(() => {
        this.form.deletedImages.push(mediaId);
        this.media.splice(index, 1);
      }).catch();
    },
    handleExceed(files, fileList) {
      this.$message.warning(`The limit is 3, you selected ${files.length} files this time, add up to ${files.length + fileList.length} totally`);
    },
    beforeRemove(file, fileList) {
      return this.$confirm(`Delete this image?`);
    },
    // handleUpload(file) {
    //   console.log(file);
    // },
    handleAvatarSuccess(res, file) {
      this.form.images.push(res.media_id);
      // this.form.m = URL.createObjectURL(file.raw);
    },
    beforeAvatarUpload(file) {
      // console.log(file);
      const isJPG = file.type === 'image/jpeg';
      const isPNG = file.type === 'image/png';
      const isLt2M = file.size / 1024 / 1024 <= 7;
      // if (isJPG || isPNG) {
      //   this.form.images.push(file);
      // }
      if (!isJPG && !isPNG) {
        this.$message.error('Picture must be a picture in JPG or PNG format!');
      }
      if (!isLt2M) {
        this.$message.error('Picture size can not exceed 2MB!');
      }
      return (isJPG || isPNG) && isLt2M;
    },
    addProduct() {
      const app = this;
      app.load = true;
      const params = app.form;
      params.discounts = app.discounts;
      createProduct.store(params)
        .then(response => {
          app.$message({ message: 'New Product Added Successfully!!!', type: 'success' });
          app.items.push(response.item);
          app.resetForm();
          app.$emit('update', response);
          app.load = false;
        })
        .catch(error => {
          app.load = false;
          alert(error.message);
        });
    },
    editProduct() {
      const app = this;
      const updateProduct = new Resource('stock/general-items/update');
      app.load = true;
      var params = app.form;
      params.discounts = app.discounts;
      updateProduct.update(params.id, params)
        .then(response => {
          app.$message({ message: 'Product Updated Successfully!!!', type: 'success' });
          app.$emit('update', response.item);

          app.load = false;
        })
        .catch(error => {
          app.load = false;
          console.log(error.message);
        });
    },
    resetForm() {
      const app = this;
      app.discounts = [];
      app.form = {
        name: '',
        package_type: '',
        // sku: '',
        category_id: '',
        description: '',
        picture: '/images/no-image.jpeg',
        currency_id: 1,
        images: [],
        deletedImages: [],
        // tax_ids: [],
        // purchase_price: '',
        amount: 0,
        quantity_stocked: 0,
      };
      app.fileList = [];
    },

  },
};
</script>
