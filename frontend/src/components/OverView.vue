<template>
  <div class="tab-pane active" style="width: 100%; max-width: 768px">
    <div class="d-flex flex-row align-items-center justify-content-between">
      <h4>OverView</h4>
    </div>
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
};
</script>