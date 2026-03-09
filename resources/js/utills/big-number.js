import BigNumber from 'bignumber.js';
import moment from "moment";
const PRECISION = 4;

export function toFixed(a, b, precision) {
  if (toBigNumber(a).isZero() || toBigNumber(b).isZero()) {
    return '0';
  }
  return toBigNumber(a)
    .div(toBigNumber(b))
    .toFormat(precision ?? PRECISION);
}

export function formatUnits(
  a,
  decimals,
  precision
) {
  if (!decimals) return '';
  return toFixed(a, toBigNumber(10).pow(decimals), precision);
}

export function toBigNumber(n) {
  return new BigNumber(n.toString());
}
export function formatTimestamp(unix) {
  console.log('moment=>>>',moment.unix(unix))
  return moment.unix(unix).local().format("ddd, MMM Do YYYY, h:mm:ss a");
}
