<template>
  <div id="revenuedashboard" class="revenuedashboard">

    <h2>Last {{chartMonths}} months’ income</h2>
    <ul class="months">
      <li v-for="i in [6, 12, 18, 24, 36, 48]" :key="i">
        <a href
           v-show="i !== chartMonths"
           @click.prevent="chartMonths = i"
           >{{i}} months</a>
        <span
           v-show="i === chartMonths"
           >{{i}} months</span>
      </li>
    </ul>

    <area-chart
      :data="incomeAndChurnChartData.data"
      :dataset="incomeAndChurnChartData.dataset"
      :library="incomeAndChurnChartData.library"
      :curve="curve"
      :min="incomeAndChurnChartData.min"
      ></area-chart>

    <h2>Current Key Indicators</h2>
    <div class="bigstats">
      <div>
        <div class="bignums two">
          <h2>Monthly Donors</h2>
          <div class="l">
            <div class="bignum" >{{formatNum(latestFull.regularDonorCount, 0)}}</div>
            <div class="othernum" >Donor count</div>
          </div>
          <div class="r">
            <div class="bignum" >£{{formatNum(latestFull.regularDonorAvgAmount, 2)}}</div>
            <div class="othernum" >Average donation</div>
          </div>
        </div>
      </div>

      <div>
        <div class="bignums">
          <h2>Year to Date</h2>
          <div>
            <div class="bignum" >£{{formatNum(latest.thisYearTotal, 0)}}</div>
            <div class="othernum" >&nbsp;</div>
          </div>
        </div>
      </div>
    </div>

    <h2>Quarterly Income Summary to-date</h2>
    <quarterly-table :latest="latest" ></quarterly-table>

    <h2>Period</h2>
    <p>Most reports below show figures for a particular month, which you can select here. The <em>Donations</em> report can optionally show figures across a range of months.</p>
    <label for="range-end">Select month (Range end)</label>
    <select id="range-end"
      v-model="latestFull"
      >
      <option v-for="(m, i) in fullMonths"
        :value="m"
               >{{ formatDateAsMonthYear(m.period[0]) }}</option>
    </select>

    <h2>Donations in {{ (rangeStart !== '') ? formatDateAsMonthYear(rangeStart.period[0]) + ' - ' + selectedMonth : selectedMonth }}</h2>

    <label for="range-start">Range start</label>
    <select id="range-start"
      v-model="rangeStart"
      >
      <option value="">(Selected month only)</option>
      <option v-for="m in validRangeStartOptions"
        :value="m"
        :disabled="m.period[0] >= latestFull.period[0]"
               >{{ formatDateAsMonthYear(m.period[0]) }}</option>
    </select>
    <br />
    <br />

    <table>
      <thead>
        <tr>
          <th></th>
          <th colspan=2 class="center" >Count</th>
          <th colspan=2 class="center" >Average £</th>
          <th colspan=2 class="center" >Income £</th>
        </tr>
        <tr>
          <th></th>
          <th class="center">One Off</th>
          <th class="center">Regular</th>
          <th class="center">One Off</th>
          <th class="center">Regular</th>
          <th class="center">One Off</th>
          <th class="center">Regular</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="source in ['All', 'Email', 'Social', 'Website', 'Other']" :key="source" >
          <th>{{ source }}</th>
          <td v-for="t in [
            'oneOffDonorCount', 'regularDonorCount',
            'oneOffDonorAvgAmount', 'regularDonorAvgAmount',
            'oneOffDonorIncome', 'regularDonorIncome',
            ]" :key="t"
              class="right"
              >{{ donationsTable[source][t] }}</td>
        </tr>
      </tbody>
    </table>

    <h2>Recruitment, Retention for {{selectedMonth}}</h2>
    <div class="bigstats">
      <div>
        <div class="bignums two">
          <h2>Retention</h2>
          <div class="l">
            <div class="bignum" >{{Math.round(latestFull.annualRetainedRegularDonorsPercent * 10)/10}}%</div>
            <div class="othernum" >{{latestFull.annualRetainedRegularDonorsCount + ' / ' + latestFull.annualPreviousRegularDonorsCount}}<br />
            last year ({{latestFull.annualChurnPercent}}% churn)</div>
          </div>
          <div class="r">
            <div class="bignum" >{{Math.round(latestFull.monthlyRetainedRegularDonorsPercent*10)/10}}%</div>
            <div class="othernum" >{{latestFull.monthlyRetainedRegularDonorsCount + ' / ' + latestFull.monthlyPreviousRegularDonorsCount}}<br />
            last month ({{latestFull.churnPercent}}% churn)</div>
          </div>
        </div>
      </div>
      <div>
        <div class="bignums two">
          <h2>Recruitment</h2>
          <div class="l">
            <div class="bignum" >{{Math.round(latestFull.annualRecruitmentPercent)}}%</div>
            <div class="othernum" >+{{latestFull.annualNewDonors}}<br />
              last year
            </div>
          </div>
          <div class="r">
            <div class="bignum" >{{Math.round(latestFull.monthlyRecruitmentPercent)}}%</div>
            <div class="othernum" >+{{latestFull.monthlyNewDonors}}<br />
              last month
            </div>
          </div>
        </div>
      </div>

      <div>
        <div class="bignums two">
          <h2>Standard Stats</h2>
          <div class="l">
            <div class="bignum" >£{{Math.round(latestFull.MRR).toLocaleString()}}</div>
            <div class="othernum" >MRR</div>
          </div>
          <div class="r">
            <div class="bignum" >£{{Math.round(latestFull.LTV).toLocaleString()}}</div>
            <div class="othernum" >LTV</div>
          </div>
        </div>
      </div>

    </div><!-- /.bigstats -->

    <h2>One Off Donations in {{selectedMonth}}</h2>

    <div class="ood">
      <div class="first"
           :style="{ width: oodFirstsPercent + '%'}">
        <span class="qty" >{{latestFull.oneOffDonors1st || 0}} donations</span>
        <span class="bar" style="width: 100%">First donation</span>
      </div>
      <div class="repeats"
           :style="{left: oodFirstsPercent + '%', width: oodRepeatsPercent + '%'}">
        <span class="qty" >{{latestFull.oneOffDonorsRepeat || 0}} repeat donations</span>
        <span class="bar"
          v-for="r in oodRepeatsParts"
          :style="r.style"
          :title="r.title"
          :key="r.title" >{{ r.title }}</span>
      </div>
    </div>

    <!-- country -->
    <table border="">
      <thead>
        <tr><th>Donations</th><th>Country</th></tr>
      </thead>
      <tbody>
        <tr v-for="row in latestFull.oneOffTopCountries" :key="row.country">
          <td><bg-barchart
            :total="latestFull.oneOffTopCountries[0].payments"
            :value="row.payments"
            :text="row.payments"
            bar=1
          ></bg-barchart></td>
          <td>{{row.country}}</td>
        </tr>
      </tbody>
    </table>

  </div>
</template>
<style lang="scss">

$strongBlue: #0162B7; //= hsl(208, 99%, 36%)
.revenuedashboard .center { text-align: center; }
.revenuedashboard .right { text-align: right; }

.period-selectors {
  display: flex;
  margin: 0 -1rem;
  justify-content: flex-start;
  flex-wrap: wrap;
  &>* {
    flex: 0 0 auto;
    padding: 0 1rem;
  }
}

// barcharts
.revenuedashboard .bgbar {
  position: relative;
}
.revenuedashboard .bgbar .bar {
  background: #BFDCF7;
  height: 100%;
  position: absolute;
  right: 0;
}
.revenuedashboard .bgbar .text {
  position: relative;
  text-align: right;
}

.revenuedashboard ul.months {
  margin:0;
  padding:0;
  list-style: none;
}
.revenuedashboard ul.months li {
  margin: 0;
  padding: 0;
  display: inline-block;
  line-height: 1.5;
}
.revenuedashboard ul.months a,
.revenuedashboard ul.months span {
  margin-right: 2rem;
  width: 5rem;
  display: inline-block;
}

.revenuedashboard .bigstats {
  display: flex;
  flex-wrap: wrap;
  margin: 1rem -1rem;
}
.revenuedashboard .bigstats>div {
  flex: 1 0 auto;
  padding: 0 1rem;
  margin-bottom: 2rem;
}
.revenuedashboard .bigstats>div>div { height: 100%; }

.revenuedashboard .bignums {
  margin: 0;
  padding: 2rem 1rem;
  display: grid;
  grid-template-columns: 1fr;
  grid-gap: 1rem;
  gap: 1rem;

  background: white;
  text-align: center;
}
.revenuedashboard .bignums>h2 {
  margin: 0;
  line-height: 1;
  font-size: 1.7rem;
}

/* Two columns */
.revenuedashboard .bignums.two {
  width: auto; /* civicrm.css adds a 2em size to .two! */
  grid-template-columns: 1fr 1fr;
}
.revenuedashboard .bignums.two>h2 {
  grid-column: 1 / 3 ;
}

.revenuedashboard .bignum {
  padding: 0.5rem 0;
  font-size: 3rem;
  line-height: 1;
  font-weight: bold;
  text-align: center;
}
.revenuedashboard .othernum {
  padding: 0;
}

.revenuedashboard .ood {
  outline: solid 1px #eee;
  position: relative;
  height: 4rem;
  padding-top:2rem;
  margin-bottom: 2rem;
}
.revenuedashboard .ood>div {
  position: absolute;
  height: 2rem;
}
.revenuedashboard .ood .qty {
  width: 100%;
  position: absolute;
  top: -1.8rem;
  text-align: center;
}
.revenuedashboard .ood .bar {
  min-height: 2rem;
  position: absolute;
  text-align: center;
  line-height: 2rem;
}
.revenuedashboard .ood .first .qty {
  border-bottom:solid 2px hsl(208, 80%, 60%);
}
.revenuedashboard .ood .repeats .qty {
  border-bottom:solid 2px hsl(208, 80%, 40%);
}
.revenuedashboard .ood .first .bar {
  background: hsl(208, 80%, 90%);
}
.revenuedashboard .ood .repeats .bar {
  background: hsl(208, 80%, 80%);
}

</style>
<script>
import QuarterlyTable from './QuarterlyTable.vue';
import BgBarchart from './BgBarchart.vue';

export default {
  props: ['config'],
  components: {QuarterlyTable, BgBarchart},
  data() {
    const data = this.config;
    data.rangeStart = '';

    var i = data.all.length - 1;
    data.latest = data.all[i];

    while ((i > 0) && data.all[i].period[2] !== 'full') {
      i--;
    }
    data.latestFull = (i < 0) ? null : data.all[i];

    data.colours = [
      'rgba(190, 93, 93, 1)', // Red
      'rgba(148, 204, 100, 1)', // Green
      'rgba(190, 204, 101, 1)', // Amber
    ];
    data.coloursHsl = [
      [5, 50, 50],
      [80, 55, 42],
      [65, 50, 50],
    ];
    data.bgColour = 0.25;

    data.curve = false;
    data.up = 0;

    data.chartMonths = 18;

    return data;
  },
  computed: {
    validRangeStartOptions() {
      const opts = this.all.slice(0, Math.max(0, this.selectedMonthIndex));
      opts.reverse();
      return opts;
    },
    selectedMonthIndex() {
      return this.all.indexOf(this.latestFull);
    },
    rangeStartIndex() {
      return this.rangeStart === '' ? this.selectedMonthIndex : this.all.indexOf(this.rangeStart);
    },
    donationsTable() {

      var aggregates = {};
      ['All', 'Email', 'Social', 'Website', 'Other'].forEach(source => {

        const sourceStats = {};
        const sourceKey = (source === 'All') ? '' : 'Source' + source;

        [
            'oneOffDonorCount', 'regularDonorCount',
            'oneOffDonorIncome', 'regularDonorIncome',
        ].forEach(t => {

          sourceStats[t] = 0;
          for (var monthIndex = this.rangeStartIndex; monthIndex <= this.selectedMonthIndex; monthIndex++) {
            var m = this.all[monthIndex];
            sourceStats[t] += parseFloat(m[t + sourceKey] || 0);
          }
        });

        // Now calculate average.
        sourceStats['oneOffDonorAvgAmount'] = (sourceStats['oneOffDonorCount'] > 0)
          ? sourceStats['oneOffDonorIncome'] / sourceStats['oneOffDonorCount']
          : null;
        sourceStats['regularDonorAvgAmount'] = (sourceStats['regularDonorCount'] > 0)
          ? sourceStats['regularDonorIncome'] / sourceStats['regularDonorCount']
          : null;

        // Finally, format everything.
        sourceStats['oneOffDonorCount'] = sourceStats['oneOffDonorCount'].toLocaleString();
        sourceStats['oneOffDonorIncome'] = '£' + sourceStats['oneOffDonorIncome'].toLocaleFixed(0);
        sourceStats['regularDonorIncome'] = '£' + sourceStats['regularDonorIncome'].toLocaleFixed(0);
        sourceStats['regularDonorCount'] = sourceStats['regularDonorCount'].toLocaleString();

        if (sourceStats['oneOffDonorAvgAmount'] !== null) {
          sourceStats['oneOffDonorAvgAmount'] = '£' + sourceStats['oneOffDonorAvgAmount'].toLocaleFixed(2);
        }

        if (sourceStats['regularDonorAvgAmount'] !== null) {
          sourceStats['regularDonorAvgAmount'] = '£' + sourceStats['regularDonorAvgAmount'].toLocaleFixed(2);
        }

        // Store sourceStats
        aggregates[source] = sourceStats;
      });
      return aggregates;
    },
    fullMonths() {
      const f = this.all.filter(m => m.period[2] === 'full');
      f.reverse();
      return f;
    },
    otLatest() {
      var l = {};
      Object.keys(this.latest).forEach(k => {
        if (k.match(/^OT/)) {
          var k2 = k.substr(2);
          l[k2] = this.latest[k];
        }
      })
      return l;
    },
    incomeAndChurnChartData() {
      console.log("Recalculating incomeAndChurnChartData");
      window.x = this;

      // This dataset has three items indexed as: 0: Churn, 1: MRR, 2: One Offs.
      const d = [
        {name: "Churn", data: {}, dataset:    {fill: 'origin',}},
        {name: "MRR", data: {}, dataset:      {fill: 'origin',}},
        {name: "One Offs", data: {}, dataset: {fill: '-1',    }}
      ];

      // Apply colours to datasets.
      for (var i = 0; i<3; i++) {
        var c = hslaToRgba(this.coloursHsl[i], 1);
        var bg = hslaToRgba(this.coloursHsl[i], this.bgColour);
        d[i].dataset.backgroundColor = bg;
        d[i].dataset.hoverBackgroundColor = bg;
        d[i].dataset.pointBackgroundColor = bg;
        d[i].dataset.pointHoverBackgroundColor = bg;
        d[i].dataset.backgroundColor = bg;
        d[i].dataset.hoverBackgroundColor = bg;
        d[i].dataset.pointHoverBackgroundColor = bg;

        d[i].dataset.borderColor = c;
        d[i].dataset.hoverBorderColor = c;

        d[i].dataset.pointBorderColor = c;
        d[i].dataset.pointHoverBorderColor = c;
      };

      // Full dataset range.
      var min = 0, max=0;
      var l = this.all.length-1;
      if (this.all[l].period[2] !== 'full') {
        // Ignore latest partial data.
        l--;
      }
      console.log("Chart data details after filtering for full months", {l, all:this.all, period: this.all[l].period});
      for (var seriesIndex=Math.max(0, l - this.chartMonths); seriesIndex<=l; seriesIndex++) {
        var series = this.all[seriesIndex];
        if (series === undefined) {
          console.log("series", series, " undefined in ", this.all);
        }
        const x = series.period[0];
        // Churn and chart min.
        d[0].data[x] = parseFloat(series.monthlyChurnAmount || 0);
        min = Math.min(min, d[0].data[x]);

        d[1].data[x] = parseFloat(series.regularDonorIncome || 0);
        // Show one off stacked on top of regular.
        d[2].data[x] = parseFloat(series.oneOffDonorIncome || 0) + d[1].data[x];
        max = Math.max(max, d[2].data[x]);
      }
      console.log("incomeAndChurnChartData: ", d);

      // Require negative to be at least 10% of positive. (or the label gets squashed)
      min = Math.min(Math.abs(max) * -0.1, min);
      // Round min to next 1000
      min = Math.floor(min/1000) * 1000;
      console.log({min,max});
      return {
        min,
        data: d,
        dataset: [
          { fill: 'origin' },
          { fill: '-1'},
          { fill: 'origin' },
        ],
        library: {
          tooltips: {
            callbacks: {
              label(tooltipItem, data) {
                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                var val = tooltipItem.yLabel;

                if (tooltipItem.datasetIndex === 2) {
                  // When presenting the One-offs we need to subtract the regulars.
                  // As that was only there to stack the chart.
                  val -= data.datasets[1].data[tooltipItem.index];
                }
                if (label) {
                  label += ': ';
                }
                label += Math.round(val).toLocaleString();
                return label;
              }
            }
          }
        }
      };
    },
    oodFirstsPercent() {
      return parseInt(this.latestFull.oneOffDonors1st) / (parseInt(this.latestFull.oneOffDonors1st) + parseInt(this.latestFull.oneOffDonorsRepeat)) * 100.0;
    },
    oodRepeatsPercent() {
      return parseInt(this.latestFull.oneOffDonorsRepeat) / (parseInt(this.latestFull.oneOffDonors1st) + parseInt(this.latestFull.oneOffDonorsRepeat)) * 100.0;
    },
    oodRepeatsParts() {
      var t = parseInt(this.latestFull.oneOffDonorsRepeat) / 100.0;
      var l = 0;
      var n = 0;
      const parts = [];
      ['2nd', '3rd', '4th', '5OrMore'].forEach(th => {
        var v = this.latestFull['oneOffDonors' + th] || 0;
        v = parseInt(v);
        if (v > 0) {
          parts.push({
            style: {
              left: l + '%',
              width: (v/t) + '%',
              background: hslToHex(208, 60, 50+8*n),
            },
            title: th
          });
          l += v/t;
          n += 1;
        }
      });
      return parts;
    },
    selectedMonth() {
      return this.formatDateAsMonthYear(this.latestFull.period[0]);
    }
  },
  watch: {
    latestFull: function(val, oldVal) {
      if (this.rangeStart && this.rangeStart.period[0] >= val.period[0]) {
        this.rangeStart = '';
      }
    }
  },
  methods: {
    formatPercentage(stat) {
      return Math.round(data.latest[stat]) + '%';
    },
    formatSourceValue(source, t) {
      if (source) {
        source = 'Source' + source;
      }
      const key = t + source;
      if (key in this.latestFull) {
        return parseFloat(this.latestFull[key]).toLocaleString();
      }
      return '';
    },
    formatNum(v, dp) {
      return parseFloat(v).toLocaleFixed(dp);
    },
    formatDateAsMonthYear(iso8601string) {
      if (!iso8601string) {
        return '';
      }
      const pretendUTCTime = iso8601string.replace(/(\+\d\d:\d\d)?$/, '+00:00');
      const d = new Date(pretendUTCTime);
      return d.toUTCString().replace(/^\w+, \d+ (\w+ \d+).*$/, '$1');
    }
  }
};
</script>

