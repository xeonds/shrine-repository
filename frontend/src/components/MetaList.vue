<template>
  <div class="tab-pane active" id="home" style="width: 100%; max-width: 768px">
    <div class="d-flex flex-row align-items-center justify-content-between">
      <h4>{{ $route.params.id == "" ? "Home" : $route.params.id }}</h4>
      <!-- <button
        href="#"
        class="btn btn-primary"
        data-toggle="modal"
        data-target="#createMeta"
      >
        New
      </button> -->
    </div>
    <br />
    <div class="d-flex flex-row flex-wrap">
      <meta-box
        v-for="meta in metalist"
        :key="meta.id"
        :meta="meta"
        :expand-len="32"
      />
    </div>
  </div>
</template>

<style scoped>
</style>

<script>
import axios from "axios";
import MetaBox from "./MetaBox.vue";

axios.defaults.baseURL = "http://www.jiujiuer.xyz/pages/repo-tr/";

export default {
  name: "HomeView",
  components: { "meta-box": MetaBox },
  data: function () {
    return {
      metalist: [],
    };
  },
  created: async function () {
    var baseURL = "core.php?api&v1&";
    var tmp = [];
    var that = this;

    tmp = await axios.get(baseURL + "meta&get_meta");
    that.metalist = tmp.data.data;
    that.metaFilter();
  },
  async beforeRouteUpdate() {
    this.metaFilter();
  },
  methods: {
    metaFilter: function () {
      var that = this;
      var tmp = [];

      that.metalist.forEach((element) => {
        if (element.tag.includes(this.$route.params.id)) {
          tmp.push(element);
        }
      });
      that.metalist = tmp;
    },
    viewMeta: function (meta) {
      var that = this;

      that.meta.view = Object.assign({}, meta);
      if (that.meta.view.type == "text") {
        // that.meta.view.content = marked(that.meta.view.content);
      }
    },
  },
};
</script>
