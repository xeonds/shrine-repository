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
        const date = new Date(time * 1000); // 时间戳为10位需*1000，时间戳为13位的话不需乘1000
        const dateNumFun = (num) => (num < 10 ? `0${num}` : num); // 使用箭头函数和三目运算以及es6字符串的简单操作。因为只有一个操作不需要{} ，目的就是数字小于10，例如9那么就加上一个0，变成09，否则就返回本身。        // 这是es6的解构赋值。
        const [Y, M, D, h, m, s] = [
          date.getFullYear(),
          dateNumFun(date.getMonth() + 1),
          dateNumFun(date.getDate()),
          dateNumFun(date.getHours()),
          dateNumFun(date.getMinutes()),
          dateNumFun(date.getSeconds()),
        ];
        return `${Y}-${M}-${D} ${h}:${m}:${s}`; // 一定要注意是反引号，否则无效。
      }
    },
  },
};
</script>