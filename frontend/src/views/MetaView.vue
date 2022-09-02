<template>
  <div>
    <h1>{{ meta.title }}</h1>
    <div v-html="meta.html"></div>
  </div>
</template>

<style scoped>
</style>

<script>
import axios from "axios";
import { marked } from "marked";

axios.defaults.baseURL = "http://www.jiujiuer.xyz/pages/repo-tr/";

export default {
  name: "MetaView",
  data: function () {
    return {
      meta_id: this.$route.params.meta_id,
      meta: { time: "", type: "", tag: "", html: "" },
    };
  },
  created: async function () {
    let data = new FormData();

    data.append("id", this.$route.params.meta_id);
    var meta = await axios.post("core.php?api&v1&meta&get_meta", data);
    this.meta = meta.data.data;
    this.meta.html = marked(this.meta.content);
  },
};
</script>


