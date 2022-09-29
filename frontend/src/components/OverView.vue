<template>
  <div
    id="main"
    class="d-flex flex-column align-items-begin justify-content-center"
  >
    <h4>OverView</h4>
    <div>
      <h5>Recent Meta</h5>
      <meta-list></meta-list>
    </div>
  </div>
</template>

<style scoped>
</style>

<script>
import MetaList from "./MetaList.vue";

export default {
  name: "OverView",
  components: { "meta-list": MetaList },
  data: function () {
    return {};
  },
  created: async function () {
    // var baseURL = "index.php?api&v1&";
    // var tmp = "";
    // var that = this;
    // tmp = await axios.get(baseURL + "config&get");
    // that.config = tmp.data.data;
    // tmp = await axios.get(baseURL + "meta&get_meta");
    // that.meta.metalist = tmp.data.data;
    // that.getTagList();
  },
  methods: {},
};
</script>