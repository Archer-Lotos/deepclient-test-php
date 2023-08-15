'''
from deepclient import DeepClient, DeepClientOptions
from gql import gql, Client
from gql.transport.aiohttp import AIOHTTPTransport
'''

from typing import Any, Optional, Union, Dict, List

class DeepClient:
    def select(self, exp: Union[Dict, int, List[int]], options: Dict = {}) -> Dict:
        return "test"

def make_deep_client(token, url):
    if not token:
        raise ValueError("No token provided")
    if not url:
        raise ValueError("No url provided")
    return DeepClient()

    '''
    transport = AIOHTTPTransport(url=url, headers={'Authorization': f"Bearer {token}"})
    client = Client(transport=transport, fetch_schema_from_transport=True)
    options = DeepClientOptions(gql_client=client)
    deep_client = DeepClient(options)
    return deep_client
    '''

