<template>
  <div>OverView</div>
</template>

<style scoped>
</style>

<script>
export default {
  name: "OverView",
  components: {},
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
  methods: {
    getTimestamp: function () {
      return new Date().getTime();
    },
  },
};
</script>