<template>
  <div class="d-flex flex-row justify-content-center w-100">
    <div id="meta-list" class="list-group">
      <template v-for="(meta, index) in metaList" :key="index">
        <a class="list-group-item list-group-item-action">
          <h5>{{ meta.title }}</h5>
          <date-filter
            :timestamp="meta.time"
            class="text-secondary"
          ></date-filter>
        </a>
      </template>
    </div>
    <div id="meta-editor" class="bg-light">
      <div id="editor-bar">editor-bar</div>
      <div id="meta-title">{{ editor.title }}</div>
      <div id="meta-content">{{ editor.content }}</div>
    </div>
  </div>
</template>

<style scoped>
#meta-list {
  width: 25%;
  margin-right: 2rem;
  max-width: 20rem;
}

#meta-editor {
  width: 70%;
  max-width: 70rem;
  border-radius: 0.25rem;
}
#meta-list,
#meta-editor {
  margin-top: 2rem;
  margin-bottom: 2rem;
}
</style>

<script>
import DateFilter from "./DateFilter.vue";

export default {
  name: "MetaEditor",
  components: { "date-filter": DateFilter },
  data: function () {
    return {
      metaList: [
        { title: "testMetaA", time: 1660000000 },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
        { title: "testMetaA", time: Date.now() },
      ],
      editor: {
        title: "",
        date: "",
        author: "",
        tag: "",
        category: "",
        content: "Choose or create an article to edit...",
      },
    };
  },
  created: function () {},
  methods: {},
};
</script>