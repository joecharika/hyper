<?php


namespace Hyper\Application\api {

    use Exception;
    use Hyper\{Application\Annotations\action, Application\Http\Request};

    /**
     * Class HyperApiCrudController
     * @package Hyper\standalone\api
     */
    class HyperApiCrudController extends HyperApiController
    {

        /**
         * @action index
         * @param Request $request
         * @param int|null $id
         * @return HyperApiResponse
         */
        public function getIndex(Request $request, ?int $id = null): HyperApiResponse
        {
            if (!is_null($id)) return $this->getDetails($request, $id);

            return $this->ok($this->db->all()->toList());
        }

        /**
         * @action details
         * @param Request $request
         * @param int $id
         * @return HyperApiResponse
         */
        public function getDetails(Request $request, int $id): HyperApiResponse
        {
            $item = $this->db->first('id', $id);

            if (is_null($item)) return $this->notFound();

            return $this->ok($item);
        }

        /**
         * @action update
         * @param Request $request
         * @param $item
         * @return HyperApiResponse
         * @throws Exception
         */
        public function putIndex(Request $request, $item)
        {
            if ($this->db->update($item))
                return $this->ok(['status' => true, 'message' => 'Entity updated']);

            return $this->internalServerError(['status' => false, 'message' => 'Failed to add entity']);
        }

        /**
         * @action update
         * @param Request $request
         * @param $item
         * @return HyperApiResponse
         * @throws Exception
         */
        public function patchIndex(Request $request, $item)
        {
            return $this->putUpdate($request, $item);
        }

        /**
         * @action add
         * @param Request $request
         * @param $item
         * @return HyperApiResponse
         * @throws Exception
         */
        public function postIndex(Request $request, $item)
        {
            if ($this->db->add($item))
                return $this->created(['status' => true, 'message' => 'Entity saved']);

            return $this->internalServerError(['status' => false, 'message' => 'Failed to add entity']);
        }

        /**
         * @action delete
         * @param Request $request
         * @param $id
         * @return HyperApiResponse
         * @throws Exception
         */
        public function deleteIndex(Request $request, $id)
        {
            return $this->ok(['status' => true, 'message' => "Deleted {$this->db->delete($id)}"]);
        }
    }
}