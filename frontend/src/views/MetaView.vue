<template>
  <div>
    <h1>{{ meta_id }}</h1>
  </div>
</template>

<style scoped>
</style>

<script>
import axios from "axios";

export default {
  name: "MetaView",
  data: function () {
    return {
      meta_id: "",
      meta: [],
    };
  },
  created: async function () {
    let data = new FormData();
    let meta = [];

    data.append("id", this.$route.params.meta_id);
    meta = await axios.post("meta&get_meta", data);
    this.meta = meta;
  },
};
</script>


