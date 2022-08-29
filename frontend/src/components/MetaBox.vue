<template>
  <div
    class="metaBox"
    data-toggle="modal"
    data-target="#viewMeta"
    @click="$emit('meta-clicked', meta)"
  >
    <template v-if="meta.type == 'text'">
      <h4>{{ meta.title }}</h4>
      <p>{{ meta.content.slice(0, expandLen) }}</p>
    </template>
    <template v-if="meta.type == 'file'">
      <h4 class="text-primary">{{ meta.fileName }}</h4>
      <p>File type: {{ meta.fileName.split(".")[1] }}</p>
    </template>
    <span>{{ meta.type }}</span
    ><span class="text-primary"> · </span
    ><span :key="tag" v-for="tag in meta.tag"
      ><span class="text-primary">#</span>{{ tag }}&nbsp;</span
    >
  </div>
</template>

<script>
export default {
  name: "MetaBox",
  props: ["meta", "expandLen"],
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.metaBox {
  padding: 0.8rem;
  margin-top: 0.8rem;
  margin-bottom: 0.8rem;
  margin-left: 0.5rem;
  margin-right: 0.5rem;
  word-break: break-all;
  border-radius: 12px;
  background: #f2f2f2;
  box-shadow: 8px 8px 16px #e7e7e7, -8px -8px 16px #ffffff;
}
</style>
