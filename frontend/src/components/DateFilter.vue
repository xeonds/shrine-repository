<template>
  <span>{{ dateFilter(timestamp) }}</span>
</template>

<script>
//时间戳转换

export default {
  name: "DateFilter",
  props: ["timestamp"],
  methods: {
    dateFilter: function (time) {
      if (!time) {
        return "";
      } else {
        const date =
          time.toString().length != 13 ? new Date(time * 1000) : new Date(time);
        const dateNumFun = (num) => (num < 10 ? `0${num}` : num);
        const [Y, M, D, h, m, s] = [
          date.getFullYear(),
          dateNumFun(date.getMonth() + 1),
          dateNumFun(date.getDate()),
          dateNumFun(date.getHours()),
          dateNumFun(date.getMinutes()),
          dateNumFun(date.getSeconds()),
        ];
        return `${Y}-${M}-${D} ${h}:${m}:${s}`;
      }
    },
  },
};
</script>