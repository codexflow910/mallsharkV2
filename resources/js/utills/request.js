import fetch from 'unfetch';
import qs from 'query-string';

export async function get(url, query) {
  if (query) {
    url += '?' + qs.stringify(query);
  }
  return await (await fetch(url)).json();
}

export function subgraph(subgraphUrl) {
  return async function (query, variables) {
    const res = await fetch(subgraphUrl, {
      method: 'POST',
      body: JSON.stringify({ query, variables }),
    });
    const { data } = await res.json();
    return data;
  };
}
