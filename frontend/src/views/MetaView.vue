<template>
  <div class="container-fluid" style="padding: 0">
    <div
      id="header"
      class="
        d-flex
        flex-column
        align-items-center
        justify-content-center
        bg-light
        w-100
      "
      style="padding: 2rem"
    >
      <h1>{{ meta.title }}</h1>
      <p class="text-secondary">
        <span class="text-primary">|</span> {{ Date(meta.time) }} ·
        {{ meta.author }}
      </p>
      <p class="text-secondary">
        <span class="text-primary">|</span> {{ meta.type }}
        <span class="text-primary"> · </span
        ><span :key="tag" v-for="tag in meta.tag"
          ><span class="text-primary">#</span>{{ tag }}&nbsp;</span
        >
      </p>
    </div>
    <div
      id="main"
      class="d-flex flex-column align-items-center justify-content-center"
    >
      <div id="content" v-html="meta.html" v-highlight></div>
    </div>
  </div>
</template>

<style scoped>
#content {
  padding: 2rem;
  min-height: 60vh;
  width: 80%;
  max-width: 800px;
}
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
      meta: {
        time: "",
        type: "",
        tag: "",
        html: "",
        author: "",
      },
    };
  },
  created: async function () {
    let data = new FormData();

    data.append("id", this.$route.params.meta_id);
    var meta = await axios.post("index.php?api&v1&meta&get_meta", data);
    this.meta = meta.data.data;
    this.meta.html = marked(this.meta.content);
    data.append("uid", this.meta.uid);
    var author = await axios.post("index.php?api&v1&user&get_user", data);
    this.meta.author = author.data.data.username;
  },
};
</script>
