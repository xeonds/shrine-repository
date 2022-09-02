<template>
  <div class="tab-pane active" id="home" style="width: 100%; max-width: 768px">
    <div class="d-flex flex-row align-items-center justify-content-between">
      <h4>Tags</h4>
    </div>
    <br />
    <div class="d-flex flex-row flex-wrap">
      <span v-for="tag in taglist" :key="tag">#{{ tag }}</span>
    </div>
  </div>
</template>

<style scoped>
</style>

<script>
import axios from "axios";

axios.defaults.baseURL = "http://www.jiujiuer.xyz/pages/repo-tr/";

export default {
  name: "HomeView",
  data: function () {
    return {
      metalist: [],
      taglist: [],
    };
  },
  created: async function () {
    var baseURL = "index.php?api&v1&";
    var tmp = [];
    var that = this;

    tmp = await axios.get(baseURL + "meta&get_meta");
    that.metalist = tmp.data.data;
    that.getTags();
  },
  methods: {
    getTags: function () {
      var that = this;
      that.metalist.forEach((element) => {
        element.tag.forEach((e) => {
          that.taglist.push(e);
        });
      });
      that.taglist = Array.from(new Set(that.taglist));
    },
  },
};
</script>
